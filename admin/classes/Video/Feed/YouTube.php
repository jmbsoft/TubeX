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

class Video_Feed_YouTube extends Video_Feed
{

    public function Import()
    {
        $imported = 0;
        $DB = GetDB();
        $yt = new Zend_Gdata_YouTube();
        $video_feed = $yt->getVideoFeed($this->feed['feed_url']);

        $entry;
        foreach( $video_feed as $entry )
        {
            // Check for duplicates, and skip
            if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed_history` WHERE `feed_id`=? AND `unique_id`=?', array($this->feed['feed_id'], $entry->getVideoId())) )
            {
                continue;
            }

            // Video is not embeddable, skip
            if( !$entry->isVideoEmbeddable() )
            {
                continue;
            }

            // Setup defaults
            $video = $this->defaults;

            $video['title'] = $entry->getVideoTitle();
            $video['description'] = $entry->getVideoDescription();
            $video['tags'] = Tags::Format(implode(' ', $entry->getVideoTags()));
            $video['duration'] = $entry->getVideoDuration();

            // Get preview images
            $times = array();
            $thumbs = array();
            foreach( $entry->getVideoThumbnails() as $thumb )
            {
                if( !isset($times[$thumb['time']]) )
                {
                    $times[$thumb['time']] = true;
                    $thumbs[] = array('thumbnail' => $thumb['url']);
                }
            }

            $clip = array('type' => 'Embed',
                          'clip' => '<object width="640" height="385">' .
                                    '<param name="movie" value="http://www.youtube.com/v/' . $entry->getVideoId() . '&fs=1"></param>' .
                                    '<param name="allowFullScreen" value="true"></param>' .
                                    '<param name="allowscriptaccess" value="always"></param>' .
                                    '<embed src="http://www.youtube.com/v/' . $entry->getVideoId() . '&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="640" height="385"></embed>' .
                                    '</object>');

            $best_category = GetBestCategory(join(' ', array($video['title'], $video['description'], $video['tags'])));
            if( !empty($best_category) )
            {
                $video['category_id'] = $best_category;
            }



            $video['video_id'] = DatabaseAdd('tbx_video', $video);
            DatabaseAdd('tbx_video_custom', $video);
            DatabaseAdd('tbx_video_stat', $video);

            if( !$video['is_private'] )
            {
                Tags::AddToFrequency($video['tags']);
            }

            UpdateCategoryStats($video['category_id']);

            $video_dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));

            $clip['video_id'] = $video['video_id'];
            DatabaseAdd('tbx_video_clip', $clip);

            $display_thumbnail = null;
            foreach( $thumbs as $thumb )
            {
                $thttp = new HTTP();

                if( $thttp->Get($thumb['thumbnail'], $thumb['thumbnail']) )
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
                    else
                    {
                        unlink($temp_file);
                    }
                }
            }

            if( !empty($display_thumbnail) )
            {
                $DB->Update('UPDATE `tbx_video` SET `display_thumbnail`=? WHERE `video_id`=?', array($display_thumbnail, $video['video_id']));
            }

            $DB->Update('INSERT INTO `tbx_video_feed_history` VALUES (?,?)', array($this->feed['feed_id'], $entry->getVideoId()));
            $imported++;
        }

        $DB->Update('UPDATE `tbx_video_feed` SET `date_last_read`=? WHERE `feed_id`=?', array(Database_MySQL::Now(), $this->feed['feed_id']));

        UpdateSponsorStats($this->feed['sponsor_id']);

        return $imported;
    }

    public function Test()
    {
        $yt = new Zend_Gdata_YouTube();
        $video_feed = $yt->getVideoFeed($this->feed['feed_url']);
    }
}

?>