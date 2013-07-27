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


abstract class Video_Source
{

    const UPLOAD = 'upload';
    const URL = 'url';
    const GALLERY = 'gallery';
    const EMBED = 'embed';

    const FIELD_TYPE = 'source_type';
    const FIELD_UPLOADS = 'source_uploads';
    const FIELD_URLS = 'source_urls';
    const FIELD_GALLERY = 'source_gallery';
    const FIELD_EMBED = 'source_embed';
    const FIELD_DURATION = 'source_duration';
    const FIELD_THUMBNAILS = 'source_thumbnails';

    const FLAG_HOTLINK = 'flag_hotlink';
    const FLAG_CONVERT = 'flag_convert';

    const MIN_ASPECT = 1;
    const MAX_ASPECT = 3;

    protected $source = array();

    protected $video_dir;

    protected $duration;

    protected $clips = array();

    protected $thumbs = array();

    public function __construct($source)
    {
        $this->source = $source;

        // Do not put into thumbnail generation queue if video file is not being hotlinked
        if( !Request::Get('flag_hotlink') )
        {
            $_REQUEST['flag_thumb'] = 0;
        }
    }

    public function __toString()
    {
        $output = "\nSource\n" .
                  "======\n";
        foreach( $this->source as $key => $value )
        {
            $output .= "$key: $value\n";
        }

        $output .= $this->video_dir;

        $output .= "\nClips\n" .
                   "=====\n";
        foreach( $this->clips as $clip )
        {
            $output .= "$clip\n";
        }


        $output .= "\nThumbs\n" .
                   "=====\n";
        foreach( $this->thumbs as $thumb )
        {
            $output .= "$thumb\n";
        }

        $output .= "\nDuration\n" .
                   "========\n" .
                   $this->duration;

        return $output;
    }


    // Initial processing and error checking
    abstract public function PreProcess();

    // Make sure video files, thumbs, DB information is correct
    abstract public function PostProcessSuccess($video_id);

    // Cleanup on failure
    abstract public function PostProcessFailure();
}


?>