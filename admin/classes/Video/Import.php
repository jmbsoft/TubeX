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

class Video_Import
{
    const SOURCE_UPLOAD = 'Upload';
    const SOURCE_URL = 'URL';
    const SOURCE_CLIPBOARD = 'Clipboard';

    const FIELD_URL = 'import_url';
    const FIELD_CLIPBOARD = 'import_text';
    const FIELD_UPLOAD = 'import_file';

    const ANALYZE_LINES = 5;

    public static function ProcessSource($source)
    {
        $file = File::Temporary(TEMP_DIR, 'txt');

        switch($source['source'])
        {
            case self::SOURCE_CLIPBOARD:
                if( String::IsEmpty($_REQUEST[self::FIELD_CLIPBOARD]) )
                {
                    throw new BaseException('The Clipboard field was empty');
                }

                file_put_contents($file, String::FormatNewlines($_REQUEST[self::FIELD_CLIPBOARD]));
                break;


            case self::SOURCE_UPLOAD:
                $upload = $_FILES[self::FIELD_UPLOAD];

                // Check for errors
                if( $upload['error'] != UPLOAD_ERR_OK )
                {
                    throw new BaseException(Uploads::CodeToMessage($upload['error']));
                }

                if( move_uploaded_file($upload['tmp_name'], $file) === false )
                {
                    throw new BaseException('Could not process uploaded file');
                }
                break;


            case self::SOURCE_URL:
                $http = new HTTP();

                if( $http->Get($_REQUEST[self::FIELD_URL], $_REQUEST[self::FIELD_URL]) )
                {
                    file_put_contents($file,  String::FormatNewlines($http->body));
                }
                else
                {
                    throw new BaseException('Could not access URL: ' . $http->error);
                }

                break;
        }

        return basename($file);
    }

    public static function Analyze($file, $delimiter)
    {
        $DB = GetDB();

        $fp = fopen($file, 'r');
        $line = fgets($fp);
        fclose($fp);

        if( strpos($line, $delimiter) === false )
        {
            throw new BaseException("The first line of import data does not contain text data delimited by '$delimiter' characters");
        }

        $pieces = explode($delimiter, $line);
        $guessed = array();
        $info = array('pieces' => count($pieces), 'fields' => array());

        for( $i = 0; $i < count($pieces); $i++ )
        {
            $piece = $pieces[$i];
            $field = array('value' => $piece, 'guess' => null);

            // URL of some sort
            if( preg_match('~^http(s)?://~', $piece) )
            {
                if( !isset($guessed['video_url']) && preg_match('~\.(' . VIDEO_EXTENSIONS . ')$~', $piece) )
                {
                    $field['guess'] = 'video_url';
                    $guessed['video_url'] = true;
                }
                else if( !isset($guessed['thumbnail_url']) && preg_match('~\.(' . IMAGE_EXTENSIONS . ')$~', $piece) )
                {
                    $field['guess'] = 'thumbnail_url';
                    $guessed['thumbnail_url'] = true;
                }
                else if( !isset($guessed['base_thumbnail_url']) && preg_match('~(pic|image|thumb|prev)~', $piece) )
                {
                    $field['guess'] = 'base_thumbnail_url';
                    $guessed['base_thumbnail_url'] = true;
                }
                else if( !isset($guessed['base_video_url']) )
                {
                    $field['guess'] = 'base_video_url';
                }
            }

            // Embed code
            else if( !isset($guessed['embed_code']) && preg_match('~(<|&lt;)(object|embed)~', $piece) )
            {
                $field['guess'] = 'embed_code';
                $guessed['embed_code'] = true;
            }

            // Video filename
            else if( !isset($guessed['video_filename']) && preg_match('~\.(' . VIDEO_EXTENSIONS . ')$~', $piece) )
            {
                $field['guess'] = 'video_filename';
                $guessed['video_filename'] = true;
            }

            // Thumbnail filename
            else if( !isset($guessed['thumbnail_filename']) && preg_match('~\.(' . IMAGE_EXTENSIONS . ')$~', $piece) )
            {
                $field['guess'] = 'thumbnail_filename';
                $guessed['thumbnail_filename'] = true;
            }

            // Formatted duration
            else if( !isset($guessed['duration_formatted']) && preg_match('~^\d\d:\d\d:\d\d$~', $piece) )
            {
                $field['guess'] = 'duration_formatted';
                $guessed['duration_formatted'] = true;
            }

            // Duration seconds
            else if( !isset($guessed['duration_seconds']) && preg_match('~^\d+$~', $piece) )
            {
                $field['guess'] = 'duration_seconds';
                $guessed['duration_seconds'] = true;
            }

            // Category
            else if( !isset($guessed['category']) && $DB->QueryCount('SELECT COUNT(*) FROM `tbx_category` WHERE `name` LIKE ?', array("%$piece%")) )
            {
                $field['guess'] = 'category';
                $guessed['category'] = true;
            }

            $info['fields'][] = $field;
        }

        return $info['fields'];
    }

    public static function Import($settings)
    {
        $DB = GetDB();

        ProgressBarShow('pb-import');

        $file = TEMP_DIR . '/' . File::Sanitize($settings['import_file']);
        $fp = fopen($file, 'r');
        $filesize = filesize($file);
        $line = $read = $imported = 0;
        $expected = count($settings['fields']);
        while( !feof($fp) )
        {
            $line++;
            $string = fgets($fp);
            $read += strlen($string);
            $data = explode($settings['delimiter'], trim($string));

            ProgressBarUpdate('pb-import', $read / $filesize * 100);

            // Line does not have the expected number of fields
            if( count($data) != $expected )
            {
                continue;
            }

            $video = array();
            $defaults = array('category_id' => $settings['category_id'],
                              'sponsor_id' => $settings['sponsor_id'],
                              'username' => $settings['username'],
                              'duration' => Format::DurationToSeconds($settings['duration']),
                              'status' => $settings['status'],
                              'next_status' => $settings['status'],
                              'allow_comments' => $settings['allow_comments'],
                              'allow_ratings' => $settings['allow_ratings'],
                              'allow_embedding' => $settings['allow_embedding'],
                              'is_private' => $settings['is_private'],
                              'date_added' => Database_MySQL::Now(),
                              'is_featured' => 0,
                              'is_user_submitted' => 0,
                              'conversion_failed' => 0,
                              'tags' => null,
                              'title' => null,
                              'description' => null);

            foreach( $settings['fields'] as $index => $field )
            {
                if( !empty($field) )
                {
                    $video[$field] = trim($data[$index]);
                }
            }


            // Setup clips
            $clips = array();
            $thumbs = array();
            $clip_type = 'URL';
            if( isset($video['embed_code']) )
            {
                // Cannot convert or thumbnail from embed code
                $settings['flag_convert'] = $settings['flag_thumb'] = false;

                $clips[] = $video['embed_code'];
                $clip_type = 'Embed';
            }
            else if( isset($video['gallery_url']) )
            {
                $http = new HTTP();

                if( !$http->Get($video['gallery_url']) )
                {
                    // Broken gallery URL, continue
                    continue;
                }

                list($thumbs, $clips) = Video_Source_Gallery::ExtractUrls($http->url, $http->body);
            }
            else if( !isset($video['video_url']) && isset($video['base_video_url']) )
            {
                if( !preg_match('~/$~', $video['base_video_url']) )
                {
                    $video['base_video_url'] .= '/';
                }

                foreach( explode(',', $video['video_filename']) as $filename )
                {
                    $clips[] = $video['base_video_url'] . $filename;
                }
            }
            else
            {
                $clips[] = $video['video_url'];
            }

            // Check for duplicate clips
            $duplicate = false;
            foreach( $clips as $clip )
            {
                if( !Request::Get('flag_skip_imported_check') && $DB->QueryCount('SELECT COUNT(*) FROM `tbx_imported` WHERE `video_url`=?', array($clip)) > 0 )
                {
                    $duplicate = true;
                }

                $DB->Update('REPLACE INTO `tbx_imported` VALUES (?)', array($clip));
            }

            // Dupe found
            if( $duplicate )
            {
                continue;
            }


            // Setup thumbs
            if( !isset($video['gallery_url']) && !isset($video['thumbnail_url']) && isset($video['base_thumbnail_url']) )
            {
                if( !preg_match('~/$~', $video['base_thumbnail_url']) )
                {
                    $video['base_thumbnail_url'] .= '/';
                }

                foreach( explode(',', String::FormatCommaSeparated($video['thumbnail_filename'])) as $filename )
                {
                    $thumbs[] = $video['base_thumbnail_url'] . $filename;
                }
            }
            else if( !isset($video['gallery_url']) && isset($video['thumbnail_url']) )
            {
                $thumbs[] = $video['thumbnail_url'];
            }


            // Setup duration
            if( isset($video['duration_seconds']) )
            {
                $video['duration'] = $video['duration_seconds'];
            }
            else if( isset($video['duration_formatted']) )
            {
                $video['duration'] = Format::DurationToSeconds($video['duration_formatted']);
            }


            // Use description for title
            if( empty($video['title']) )
            {
                $video['title'] = isset($video['description']) ? $video['description'] : '';
            }

            // Use title for description
            if( empty($video['description']) )
            {
                $video['description'] = isset($video['title']) ? $video['title'] : '';
            }

            // Use title for tags
            if( empty($video['tags']) )
            {
                $video['tags'] = isset($video['title']) ? $video['title'] : '';
            }


            // Setup category
            if( isset($video['category']) && ($category_id = $DB->QuerySingleColumn('SELECT `category_id` FROM `tbx_category` WHERE `name` LIKE ?', array($video['category']))) !== false )
            {
                $video['category_id'] = $category_id;
            }
            else if( ($category_id = GetBestCategory($video['title'] . ' ' . $video['description'])) !== null )
            {
                $video['category_id'] = $category_id;
            }


            // Merge in the defaults
            $video = array_merge($defaults, $video);

            // Format tags and convert to UTF-8
            $video['tags'] = Tags::Format($video['tags']);
            $video = String::ToUTF8($video);

            if( Request::Get('flag_convert') )
            {
                $video['status'] = STATUS_QUEUED;
            }

            // Add to database
            $video['video_id'] = DatabaseAdd('tbx_video', $video);
            DatabaseAdd('tbx_video_custom', $video);
            DatabaseAdd('tbx_video_stat', $video);

            if( $video['is_private'] )
            {
                $video['private_id'] = sha1(uniqid(mt_rand(), true));
                DatabaseAdd('tbx_video_private', $video);
            }

            if( $video['status'] == STATUS_QUEUED )
            {
                $video['queued'] = time();
                DatabaseAdd('tbx_conversion_queue', $video);
            }

            if( Request::Get('flag_thumb') )
            {
                $video['queued'] = time();
                DatabaseAdd('tbx_thumb_queue', $video);
            }

            if( $video['status'] == STATUS_ACTIVE && !$video['is_private'] )
            {
                Tags::AddToFrequency($video['tags']);
            }

            // Add clips
            foreach( $clips as $clip )
            {
                DatabaseAdd('tbx_video_clip', array('video_id' => $video['video_id'], 'type' => $clip_type, 'clip' => $clip));
            }


            $dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));

            // Process thumbs
            $thumb_ids = array();
            foreach( $thumbs as $thumb )
            {
                $http = new HTTP();

                if( $http->Get($thumb, $thumb) )
                {
                    if( Video_Thumbnail::CanResize() )
                    {
                        $thumb_temp = $dir->AddTempFromVar($http->body, 'jpg');
                        $thumb_file = Video_Thumbnail::Resize($thumb_temp, Config::Get('thumb_size'), Config::Get('thumb_quality'), $dir->GetThumbsDir());
                    }
                    else
                    {
                        $thumb_file = $dir->AddThumbFromVar($http->body);
                    }

                    if( !empty($thumb_file) )
                    {
                        $thumb_ids[] = DatabaseAdd('tbx_video_thumbnail', array('video_id' => $video['video_id'],
                                                                                'thumbnail' => str_replace(Config::Get('document_root'), '', $thumb_file)));
                    }
                }
            }

            // Determine number of thumbnails and select random display thumbnail
            $num_thumbnails = count($thumb_ids);
            $display_thumbnail = null;
            if( $num_thumbnails > 0 )
            {
                // Select display thumbnail randomly from the first 40%
                $display_thumbnail = $thumb_ids[rand(0, floor(0.40 * $num_thumbnails))];
            }

            DatabaseUpdate('tbx_video', array('video_id' => $video['video_id'],
                                              'num_thumbnails' => $num_thumbnails,
                                              'display_thumbnail' => $display_thumbnail));

            $imported++;
        }
        fclose($fp);

        UpdateCategoryStats();
        UpdateSponsorStats($settings['sponsor_id']);

        $t = new Template();
        $t->ClearCache('categories.tpl');

        ProgressBarHide('pb-import', NumberFormatInteger($imported) . ' videos have been imported!');


        // Start up the thumbnail and converson queues if needed
        if( !Config::Get('flag_using_cron') )
        {
            if( Request::Get('flag_convert') )
            {
                ConversionQueue::Start();
            }

            if( Request::Get('flag_thumb') )
            {
                ThumbQueue::Start();
            }
        }

        File::Delete($file);
    }
}
?>