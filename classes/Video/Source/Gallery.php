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

class Video_Source_Gallery extends Video_Source
{

    public function PreProcess()
    {
        $this->video_dir = new Video_Dir(null, 0700);
        $http = new HTTP();

        if( !$http->Get($this->source[Video_Source::FIELD_GALLERY]) )
        {
            throw new BaseException('Could not access gallery: ' . $http->error);
        }

        list($thumbs, $clips) = self::ExtractUrls($http->url, $http->body);

        if( empty($clips) )
        {
            throw new BaseException('No video files could be located on this gallery');
        }

        // Hotlinking video from gallery
        if( $this->source[Video_Source::FLAG_HOTLINK] )
        {
            $this->clips = $clips;
            $this->duration = Format::DurationToSeconds($this->source[Video_Source::FIELD_DURATION]);
        }

        // Store video locally
        else
        {
            // Download clips
            $amount = round(Config::Get('thumb_amount') / count($clips));
            foreach( $clips as $clip )
            {
                $chttp = new HTTP();

                if( $chttp->Get($clip, $http->url) )
                {
                    $clip = $this->video_dir->AddClipFromVar($chttp->body, File::Extension($chttp->url));
                    $this->clips[] = $clip;

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


        // Download thumbs from gallery if none could be created from the video files
        // or video files are being hotlinked
        if( empty($this->thumbs) )
        {
            foreach( $thumbs as $thumb )
            {
                $coords = null;
                if( preg_match('~^\[(.*?)\](.*)~', $thumb, $matches) )
                {
                    $coords = $matches[1];
                    $thumb = $matches[2];
                }

                $thttp = new HTTP();

                if( $thttp->Get($thumb, $http->url) )
                {
                    $temp_file = $this->video_dir->AddTempFromVar($thttp->body, JPG_EXTENSION);
                    $imgsize = @getimagesize($temp_file);
                    $aspect = $imgsize !== false ? $imgsize[0] / $imgsize[1] : 0;

                    if( $imgsize !== false && $aspect >= self::MIN_ASPECT && $aspect <= self::MAX_ASPECT )
                    {
                        if( Video_Thumbnail::CanResize() )
                        {
                            $this->thumbs[] = Video_Thumbnail::Resize($temp_file,
                                                                      Config::Get('thumb_size'),
                                                                      Config::Get('thumb_quality'),
                                                                      $this->video_dir->GetThumbsDir(),
                                                                      $coords);
                        }
                        else
                        {
                            $this->thumbs[] = $this->video_dir->AddThumbFromFile($temp_file, JPG_EXTENSION);
                        }
                    }
                    else
                    {
                        unlink($temp_file);
                    }
                }
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

    public static function ExtractUrls($base_url, $html)
    {
        $video_urls = array();
        $thumb_urls = array();
        $dom = new DOMDocument();
        @$dom->loadHTML($html);


        // See if a <base> tag is defined, and if so set $base_url
        $bases = $dom->getElementsByTagName('base');
        foreach( $bases as $base )
        {
            $href = $base->getAttribute('href');
            if( !empty($href) &&  preg_match('~^https?://~i', $href) )
            {
                $base_url = $href;
                break;
            }
        }


        // Check <a> tags
        $as = $dom->getElementsByTagName('a');
        foreach( $as as $a )
        {
            $href = $a->getAttribute('href');
            if( !empty($href) && preg_match('~\.(' . VIDEO_EXTENSIONS . ')(\?.*)?$~U', $href) )
            {
                $video_urls[] = RelativeToAbsolute($base_url, $href);

                $imgs = $a->getElementsBytagName('img');
                foreach( $imgs as $img )
                {
                    $src = $img->getAttribute('src');

                    if( preg_match('~\.' . JPG_EXTENSION . '(\?.*)?$~U', $src) )
                    {
                        $thumb_urls[] = RelativeToAbsolute($base_url, $src);
                    }
                }
            }
        }


        // Check <map> tags
        $maps = $dom->getElementsByTagName('map');
        $imgs = $dom->getElementsByTagName('img');
        foreach( $maps as $map )
        {
            $map_name = strtolower('#' . $map->getAttribute('name'));
            $areas = $map->getElementsByTagName('area');
            foreach( $areas as $area )
            {
                $href = $area->getAttribute('href');
                $coords = $area->getAttribute('coords');
                if( !empty($href) && preg_match('~\.(' . VIDEO_EXTENSIONS . ')(\?.*)?$~U', $href) )
                {
                    $video_urls[] = RelativeToAbsolute($base_url, $href);

                    foreach( $imgs as $img )
                    {
                        $src = $img->getAttribute('src');
                        $usemap = strtolower($img->getAttribute('usemap'));

                        if( empty($usemap) || $usemap != $map_name )
                        {
                            continue;
                        }

                        if( preg_match('~\.' . JPG_EXTENSION . '(\?.*)?$~U', $src) )
                        {
                            $thumb_urls[] = "[$coords]" . RelativeToAbsolute($base_url, $src);
                        }
                    }
                }
            }
        }

        return array(array_unique($thumb_urls), array_unique($video_urls));
    }
}

?>