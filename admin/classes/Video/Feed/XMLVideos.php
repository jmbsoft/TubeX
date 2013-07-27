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

class Video_Feed_XMLVideos extends Video_Feed
{

    private function ToUTF8($xml)
    {
        return preg_match('~encoding="utf-8"~i', $xml) ? utf8_encode($xml) : $xml;
    }

    public function Import()
    {
        $imported = 0;
        $http = new HTTP();

        if( $http->Get($this->feed['feed_url']) )
        {
            $xml = simplexml_load_string($this->ToUTF8($http->body), 'XML_Element', LIBXML_NOERROR, LIBXML_NOWARNING, LIBXML_NOCDATA);

            if( $xml !== false )
            {
                $DB = GetDB();

                foreach( $xml->xpath('//videos/video') as $xvideo )
                {
                    // Check for duplicates, and skip
                    if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed_history` WHERE `feed_id`=? AND `unique_id`=?', array($this->feed['feed_id'], $xvideo->id->val())) )
                    {
                        continue;
                    }

                    // Setup defaults
                    $video = $this->defaults;

                    $video['title'] = $xvideo->title->val();
                    $video['description'] = $xvideo->description->val();
                    $video['tags'] = Tags::Format($xvideo->tags->val());

                    if( empty($video['description']) )
                    {
                        $video['description'] = $video['title'];
                    }

                    // Process <clips>
                    $clips = array();
                    $screens = array();
                    foreach( $xvideo->xpath('./clips/clip') as $xclip )
                    {
                        $video['duration'] += $xclip->duration;

                        $clip_url = $xvideo->clip_url->val();
                        $flv = $xclip->flv->val();

                        // Account for malformed feeds where the clip_url contains the URL to the video
                        // file rather than the required root URL
                        if( strstr($clip_url, $flv) === false )
                        {
                            $clip_url = $clip_url . $flv;
                        }

                        $clips[] = array('type' => 'URL',
                                         'clip' => $clip_url);

                        foreach( $xclip->xpath('./screens/screen') as $xscreen )
                        {
                            $screen_url = $xvideo->screen_url->val();
                            $screen = $xscreen->val();

                            // Account for malformed feeds where the screen_url contains the URL to the image
                            // file rather than the required root URL
                            if( strstr($screen_url, $screen) === false )
                            {
                                $screen_url = $screen_url . $screen;
                            }

                            $screens[] = array('thumbnail' => $screen_url);
                        }
                    }

                    if( count($clips) > 0 )
                    {
                        $best_category = GetBestCategory(join(' ', array($video['title'], $video['description'], $video['tags'])));
                        if( !empty($best_category) )
                        {
                            $video['category_id'] = $best_category;
                        }

                        if( $this->feed['flag_convert'] )
                        {
                            $video['status'] = STATUS_QUEUED;
                            $video['next_status'] = $this->feed['status'];
                        }

                        $video['video_id'] = DatabaseAdd('tbx_video', $video);
                        DatabaseAdd('tbx_video_custom', $video);
                        DatabaseAdd('tbx_video_stat', $video);

                        if( !$video['is_private'] )
                        {
                            Tags::AddToFrequency($video['tags']);
                        }

                        $video['queued'] = time();
                        if( $this->feed['flag_convert'] )
                        {
                            DatabaseAdd('tbx_conversion_queue', $video);
                        }

                        if( $this->feed['flag_thumb'] )
                        {
                            DatabaseAdd('tbx_thumb_queue', $video);
                        }

                        UpdateCategoryStats($video['category_id']);

                        $video_dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));

                        foreach( $clips as $clip )
                        {
                            $clip['video_id'] = $video['video_id'];
                            DatabaseAdd('tbx_video_clip', $clip);
                        }

                        $display_thumbnail = null;

                        foreach( $screens as $screen )
                        {
                            $thttp = new HTTP();

                            if( $thttp->Get($screen['thumbnail'], $screen['thumbnail']) )
                            {
                                $temp_file = $video_dir->AddTempFromVar($thttp->body, JPG_EXTENSION);
                                $imgsize = @getimagesize($temp_file);

                                if( $imgsize !== false )
                                {
                                    if( Video_Thumbnail::CanResize() )
                                    {
                                        $local_filename = Video_Thumbnail::Resize($temp_file,
                                                                                  Config::Get('thumb_size'),
                                                                                  Config::Get('thumb_quality'),
                                                                                  $video_dir->GetThumbsDir());
                                    }
                                    else
                                    {
                                        $local_filename = $video_dir->AddThumbFromFile($temp_file, JPG_EXTENSION);
                                    }

                                    $local_filename = str_replace(Config::Get('document_root'), '', $local_filename);
                                    $thumb_id = DatabaseAdd('tbx_video_thumbnail', array('video_id' => $video['video_id'],
                                                                                         'thumbnail' => $local_filename));

                                    if( empty($display_thumbnail) )
                                    {
                                        $display_thumbnail = $thumb_id;
                                    }
                                }
                            }
                        }

                        $video_dir->ClearTemp();

                        if( !empty($display_thumbnail) )
                        {
                            $DB->Update('UPDATE `tbx_video` SET `display_thumbnail`=? WHERE `video_id`=?', array($display_thumbnail, $video['video_id']));
                        }

                        $DB->Update('INSERT INTO `tbx_video_feed_history` VALUES (?,?)', array($this->feed['feed_id'], $xvideo->id->val()));
                        $imported++;
                    }
                }

                $DB->Update('UPDATE `tbx_video_feed` SET `date_last_read`=? WHERE `feed_id`=?', array(Database_MySQL::Now(), $this->feed['feed_id']));

                UpdateSponsorStats($this->feed['sponsor_id']);
            }

            // Start up the thumbnail and converson queues if needed
            if( !Config::Get('flag_using_cron') )
            {
                if( $this->feed['flag_convert'] )
                {
                    ConversionQueue::Start();
                }

                if( $this->feed['flag_thumb'] )
                {
                    ThumbQueue::Start();
                }
            }
        }

        return $imported;
    }

    public function Test()
    {
        $http = new HTTP();

        if( $http->Get($this->feed['feed_url']) )
        {
            $xml = simplexml_load_string($this->ToUTF8($http->body), 'XML_Element', LIBXML_NOERROR, LIBXML_NOWARNING, LIBXML_NOCDATA);

            if( $xml !== false )
            {
                $xvideos = $xml->xpath('/videos');
                $xvideo = $xml->xpath('/videos/video');
                $xclips = $xml->xpath('/videos/video/clips');

                if( empty($xvideos) || empty($xvideo) || empty($xclips) )
                {
                    throw new BaseException('Sorry, this is not a valid Videos XML feed');
                }
            }
            else
            {
                $error = libxml_get_last_error();
                throw new BaseException('Invalid XML: ' . $error->message);
            }
        }
        else
        {
            throw new BaseException('Bad URL: ' . $http->error);
        }
    }
}

?>