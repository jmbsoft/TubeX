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

require_once '../includes/global.php';
//require_once 'Config.php';


class ThumbQueue extends QueueProcessor
{

    public static function Init()
    {
        self::$LOG = 'thumb-queue.log';
        self::$TABLE = 'tbx_thumb_queue';
        self::$SCRIPT = 'ThumbQueue.php';
        self::$CACHE_STATS = 'thumb-queue-stats';
        self::$CACHE_PID = 'thumb-queue-pid';
        self::$CACHE_STOP = 'thumb-queue-stop';
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
            $queue_item = $DB->Row('SELECT * FROM `tbx_thumb_queue` ORDER BY `queued` LIMIT 1');

            if( !empty($queue_item) )
            {
                $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($queue_item['video_id']));

                if( !empty($video) )
                {
                    $DB->Update('UPDATE `tbx_thumb_queue` SET `date_started`=? WHERE `video_id`=?', array(Database_MySQL::Now(), $video['video_id']));
                    $clips = $DB->FetchAll('SELECT * FROM `tbx_video_clip` WHERE `video_id`=? AND `type`!=? ORDER BY `clip_id`', array($queue_item['video_id'], 'Embed'));
                    $dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));

                    Video_FrameGrabber::SetLogFile($dir->GetBaseDir() . '/thumbnailer.log');

                    $thumb_start = time();
                    try
                    {
                        if( !empty($clips) )
                        {
                            $thumbs = array();
                            $duration = 0;

                            // Number of thumbs to create per clip
                            $amount = round(Config::Get('thumb_amount') / count($clips));

                            // Move existing thumbnails
                            $dir->MoveFiles($dir->GetThumbsDir(), $dir->GetTempDir(), JPG_EXTENSION);

                            // Process each clip
                            foreach( $clips as $clip )
                            {
                                self::Ping();

                                // Remote video
                                if( preg_match('~https?://~i', $clip['clip']) )
                                {
                                    $http = new HTTP();

                                    if( $http->Get($clip['clip'], $clip['clip']) )
                                    {
                                        $video_file = $dir->AddOriginalFromVar($http->body, File::Extension($clip['clip']));

                                        $vi = new Video_Info($video_file);
                                        $vi->Extract();
                                        $duration += $vi->length;

                                        $temp_thumbs = Video_FrameGrabber::Grab($video_file,
                                                                                $dir->GetProcessingDir(),
                                                                                $amount,
                                                                                Config::Get('thumb_quality'),
                                                                                Config::Get('thumb_size'),
                                                                                $vi);

                                        // Move generated thumbs from the processing directory
                                        foreach( $temp_thumbs as $temp_thumb )
                                        {
                                            $thumbs[] = $dir->AddThumbFromFile($temp_thumb);
                                        }

                                        @unlink($video_file);
                                    }
                                }

                                // Local video
                                else
                                {
                                    $temp_thumbs = Video_FrameGrabber::Grab($doc_root . '/' . $clip['clip'],
                                                                            $dir->GetProcessingDir(),
                                                                            $amount,
                                                                            Config::Get('thumb_quality'),
                                                                            Config::Get('thumb_size'));

                                    // Move generated thumbs from the processing directory
                                    foreach( $temp_thumbs as $temp_thumb )
                                    {
                                        $thumbs[] = $dir->AddThumbFromFile($temp_thumb);
                                    }
                                }
                            }


                            // Get the relative URL for each thumb and add to database
                            $thumb_ids = array();
                            foreach( $thumbs as $thumb )
                            {
                                $thumb = str_replace($doc_root, '', $thumb);
                                $thumb_ids[] = DatabaseAdd('tbx_video_thumbnail', array('video_id' => $video['video_id'],
                                                                                        'thumbnail' => $thumb));
                            }

                            // Determine number of thumbnails and select random display thumbnail
                            $num_thumbnails = count($thumbs);
                            $display_thumbnail = null;
                            if( $num_thumbnails > 0 )
                            {
                                // Select display thumbnail randomly from the first 40%
                                $display_thumbnail = $thumb_ids[rand(0, floor(0.40 * $num_thumbnails))];
                            }

                            $update = array('video_id' => $video['video_id'],
                                            'num_thumbnails' => $num_thumbnails,
                                            'display_thumbnail' => $display_thumbnail);

                            if( empty($video['duration']) && !empty($duration) )
                            {
                                $update['duration'] = $duration;
                            }

                            DatabaseUpdate('tbx_video', $update);

                            // Remove old thumbnails
                            $DB->Update('DELETE FROM `tbx_video_thumbnail` WHERE `video_id`=?' . (!empty($thumb_ids) ? ' AND`thumbnail_id` NOT IN (' . join(',', $thumb_ids) . ')' : ''), array($video['video_id']));
                            $dir->ClearTemp();
                        }
                    }
                    catch(Exception $e)
                    {
                        // Restore old thumbnails
                        $dir->MoveFiles($dir->GetTempDir(), $dir->GetThumbsDir(), JPG_EXTENSION);

                        Video_FrameGrabber::Log($e->getMessage() . (strtolower(get_class($e)) == 'baseexception' ? $e->getExtras() : '') . "\n" . $e->getTraceAsString());

                        self::UpdateStatsProcessed($thumb_start, $thumb_end, $queue_item['queued'], true);
                    }

                    $thumb_end = time();

                    $DB->Update('DELETE FROM `tbx_thumb_queue` WHERE `video_id`=?', array($queue_item['video_id']));

                    self::UpdateStatsProcessed($thumb_start, $thumb_end, $queue_item['queued']);
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


ThumbQueue::Init();

// Run the conversion queue if started from the command line with the correct argument
if( isset($argv[1]) && $argv[1] == ThumbQueue::ARGUMENT )
{
    ThumbQueue::Run();
}


?>