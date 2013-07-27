<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

if( !class_exists('Config', false) )
{
    require_once '../includes/global.php';
}

//require_once 'Config.php';


class ConversionQueue extends QueueProcessor
{

    public static function Init()
    {
        self::$LOG = 'conversion-queue.log';
        self::$TABLE = 'tbx_conversion_queue';
        self::$SCRIPT = 'ConversionQueue.php';
        self::$CACHE_STATS = 'conversion-queue-stats';
        self::$CACHE_PID = 'conversion-queue-pid';
        self::$CACHE_STOP = 'conversion-queue-stop';
    }

    public static function Run()
    {
        chdir(realpath(dirname(__FILE__) . '/../'));
        require_once('includes/global.php');

        $doc_root = Config::Get('document_root');
        $DB = GetDB();

        self::Log('Starting...');
        self::MarkRunning();

        while( true )
        {
            // See if we were requested to stop
            if( self::ShouldStop() )
            {
                self::Log('User requested stop...');
                break;
            }

            self::Ping();

            $DB->Connect();
            $queue_item = $DB->Row('SELECT *,`tbx_conversion_queue`.`video_id` AS `video_id`,`tbx_conversion_queue`.`queued` AS `queued` FROM `tbx_conversion_queue` LEFT JOIN ' .
                                   '`tbx_thumb_queue` USING (`video_id`) WHERE `tbx_thumb_queue`.`video_id` IS NULL ORDER BY `tbx_conversion_queue`.`queued` LIMIT 1');

            if( !empty($queue_item) )
            {
                $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($queue_item['video_id']));

                if( !empty($video) )
                {
                    $DB->Update('UPDATE `tbx_video` SET `conversion_failed`=0 WHERE `video_id`=?', array($video['video_id']));
                    $DB->Update('UPDATE `tbx_conversion_queue` SET `date_started`=? WHERE `video_id`=?', array(Database_MySQL::Now(), $video['video_id']));
                    $clips = $DB->FetchAll('SELECT * FROM `tbx_video_clip` WHERE `video_id`=? ORDER BY `clip_id`', array($queue_item['video_id']));
                    $dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));

                    Video_Converter::SetLogFile($dir->GetBaseDir() . '/convert.log');

                    $convert_start = time();
                    $conversion_failed = false;
                    foreach( $clips as $clip )
                    {
                        $clip_path = null;
                        $old_path = null;

                        try
                        {
                            // Stored locally, move to originals directory
                            if( $clip['clip'][0] == '/' )
                            {
                                $old_path = $doc_root . $clip['clip'];
                                $clip_path = $dir->AddOriginalFromFile($old_path);
                            }

                            // Store remotely, download to originals directory
                            else
                            {
                                $http = new HTTP();

                                if( $http->Get($clip['clip'], $clip['clip']) )
                                {
                                    $clip_path = $dir->AddOriginalFromVar($http->body, File::Extension($clip['clip']));
                                }
                                else
                                {
                                    throw new BaseException('Could not download clip for conversion: ' . $http->error);
                                }
                            }

                            $output_file = Video_Converter::Convert($clip_path,
                                                                    $dir->GetProcessingDir(),
                                                                    Config::Get('video_format'),
                                                                    Config::Get('video_bitrate'),
                                                                    Config::Get('audio_bitrate'),
                                                                    Config::Get('video_size'),
                                                                    array('ConversionQueue', 'Ping'));

                            $converted_video = $dir->AddClipFromFile($output_file);

                            $DB->Disconnect();
                            $DB->Connect();
                            $DB->Update('UPDATE `tbx_video_clip` SET `clip`=?,`filesize`=? WHERE `clip_id`=?', array(str_replace($doc_root, '', $converted_video), filesize($converted_video), $clip['clip_id']));
                        }
                        catch(Exception $e)
                        {
                            if( !empty($old_path) && !empty($clip_path) )
                            {
                                rename($clip_path, $old_path);
                            }

                            Video_Converter::Log($e->getMessage() . (strtolower(get_class($e)) == 'baseexception' ? $e->getExtras() : '') . "\n" . $e->getTraceAsString());

                            $conversion_failed = true;
                        }
                    }
                    $convert_end = time();

                    $dir->ClearProcessing();
                    $dir->ClearTemp();


                    $DB->Connect();

                    $DB->Update('DELETE FROM `tbx_conversion_queue` WHERE `video_id`=?', array($queue_item['video_id']));

                    if( $conversion_failed )
                    {
                        self::UpdateStatsProcessed($convert_start, $convert_end, $queue_item['queued'], true);
                        $DB->Update('UPDATE `tbx_video` SET `conversion_failed`=1 WHERE `video_id`=?', array($video['video_id']));
                    }
                    else
                    {
                        // Update stats
                        self::UpdateStatsProcessed($convert_start, $convert_end, $queue_item['queued']);

                        $status = empty($video['next_status']) ? STATUS_ACTIVE : $video['next_status'];

                        // Set video status
                        $DB->Update('UPDATE `tbx_video` SET `status`=? WHERE `video_id`=?',
                                    array($status,
                                          $video['video_id']));

                        if( $video['status'] != $status && $status == STATUS_ACTIVE && !$video['is_private'] )
                        {
                            Tags::AddToFrequency($video['tags']);
                        }

                        UpdateCategoryStats($video['category_id']);
                    }
                }
            }

            // No more items in the queue, let's get outta here
            else
            {
                break;
            }
        }

        self::MarkStopped();
        self::Log('Exiting...');
    }
}


ConversionQueue::Init();

// Run the conversion queue if started from the command line with the correct argument
if( isset($argv[1]) && $argv[1] == ConversionQueue::ARGUMENT )
{
    ConversionQueue::Run();
}

?>