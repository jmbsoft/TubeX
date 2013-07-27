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


class Video_Source_URL extends Video_Source
{

    public function PreProcess()
    {
        $this->video_dir = new Video_Dir(null, 0700);

        $thumbs = array();
        $clips = array();
        foreach( $this->source[Video_Source::FIELD_URLS] as $url )
        {
            $url_path = parse_url($url, PHP_URL_PATH);
            switch( File::Type($url_path) )
            {
                case File::TYPE_ZIP:
                    $http = new HTTP();

                    if( $http->Get($url, $url) )
                    {
                        $zip = $this->video_dir->AddTempFromVar($http->body, ZIP_EXTENSION);

                        foreach( Zip::ExtractEntries($zip, File::TYPE_JPEG) as $name => $data )
                        {
                            $thumbs[] = $this->video_dir->AddTempFromVar($data, JPG_EXTENSION);
                        }

                        foreach( Zip::ExtractEntries($zip, File::TYPE_VIDEO) as $name => $data )
                        {
                            $this->clips[] = $this->video_dir->AddClipFromVar($data, File::Extension($name));
                        }
                    }

                    break;

                case File::TYPE_JPEG:
                    $http = new HTTP();
                    if( $http->Get($url, $url) )
                    {
                        $thumbs[] = $this->video_dir->AddTempFromVar($http->body, JPG_EXTENSION);
                    }
                    break;

                case File::TYPE_VIDEO:
                    if( $this->source[Video_Source::FLAG_HOTLINK] )
                    {
                        $clips[] = $url;
                        $this->duration = Format::DurationToSeconds($this->source[Video_Source::FIELD_DURATION]);
                    }
                    else
                    {
                        $http = new HTTP();
                        if( $http->Get($url, $url) )
                        {
                            $this->clips[] = $this->video_dir->AddClipFromVar($http->body, File::Extension($http->url));
                        }
                    }
                    break;
            }
        }


        if( empty($clips) )
        {
            if( !empty($this->clips) && Video_Info::CanExtract() && Video_FrameGrabber::CanGrab() )
            {
                $amount = round(Config::Get('thumb_amount') / count($this->clips));
                foreach( $this->clips as $clip )
                {
                    $vi = new Video_Info($clip);
                    $vi->Extract();

                    $this->duration += $vi->length;
                    $temp_thumbs = Video_FrameGrabber::Grab($clip,
                                                            $this->video_dir->GetProcessingDir(),
                                                            $amount,
                                                            Config::Get('thumb_quality'),
                                                            Config::Get('thumb_size'));

                    // Move generated thumbs from the processing directory
                    foreach( $temp_thumbs as $temp_thumb )
                    {
                        $this->thumbs[] = $this->video_dir->AddThumbFromFile($temp_thumb);
                    }

                    $this->video_dir->ClearProcessing();
                }
            }
        }
        else
        {
            $this->clips = $clips;
        }

        if( empty($this->clips) )
        {
            throw new BaseException('No valid video URLs were submitted');
        }


        // Use images from supplied URLs if none could be generated
        if( empty($this->thumbs) && !empty($thumbs) )
        {
            if( Video_Thumbnail::CanResize() )
            {
                $this->thumbs = Video_Thumbnail::ResizeDirectory($this->video_dir->GetTempDir(),
                                                                 $this->video_dir->GetThumbsDir(),
                                                                 Config::Get('thumb_size'),
                                                                 Config::Get('thumb_quality'));
            }
            else
            {
                $this->thumbs = $this->video_dir->MoveFiles($this->video_dir->GetTempDir(), $this->video_dir->GetThumbsDir(), JPG_EXTENSION);
            }
        }

        // Cleanup temp and processing dirs
        $this->video_dir->ClearTemp();
        $this->video_dir->ClearProcessing();
    }

    public function PostProcessSuccess($video_id)
    {
        // Adjust permissions and move directory
        $doc_root = Config::Get('document_root');
        $old_directory = $this->video_dir->GetBaseDir();
        @chmod($old_directory, 0777);
        $directory = Video_Dir::DirNameFromId($video_id);
        $this->video_dir->MoveTo($directory);

        // Get the relative URL for each clip and add to database
        foreach( $this->clips as $clip )
        {
            $clip = str_replace(array($old_directory, $doc_root), array($directory, ''), $clip);
            DatabaseAdd('tbx_video_clip', array('video_id' => $video_id,
                                                'filesize' => $clip[0] == '/' ? filesize($doc_root . $clip) : 0,
                                                'clip' => $clip,
                                                'type' => 'URL'));
        }

        // Get the relative URL for each thumb and add to database
        $thumb_ids = array();
        foreach( $this->thumbs as $thumb )
        {
            $thumb = str_replace(array($old_directory, $doc_root), array($directory, ''), $thumb);
            $thumb_ids[] = DatabaseAdd('tbx_video_thumbnail', array('video_id' => $video_id,
                                                                    'thumbnail' => $thumb));
        }

        // Determine number of thumbnails and select random display thumbnail
        $num_thumbnails = count($this->thumbs);
        $display_thumbnail = null;
        if( $num_thumbnails > 0 )
        {
            // Select display thumbnail randomly from the first 40%
            $display_thumbnail = $thumb_ids[rand(0, floor(0.40 * $num_thumbnails))];
        }

        $update = array('video_id' => $video_id,
                        'num_thumbnails' => $num_thumbnails,
                        'display_thumbnail' => $display_thumbnail,
                        'duration' => $this->duration);

        DatabaseUpdate('tbx_video', $update);
    }

    public function PostProcessFailure()
    {
        if( $this->video_dir instanceof Video_Dir )
        {
            $this->video_dir->Remove();
        }
    }
}

?>
