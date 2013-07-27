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

define('TUBEX_CONTROL_PANEL', true);

require_once('includes/cp-global.php');

// Send initial response headers
NoCacheHeaders();

// Check for Firefox
$is_firefox = preg_match('~Firefox/~', $_SERVER['HTTP_USER_AGENT']);

if( Authenticate::Login() )
{
    _xVideosExport();
}
else
{
    echo "Please login to your TubeX control panel first, then access this script";
}

function _xVideosExport()
{
    $DB = GetDB();

    $result = $DB->Query('SELECT * FROM `tbx_video`');

    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename=export.txt");

    while( $video = $DB->NextRow($result) )
    {
        $clip = $DB->Row('SELECT * FROM `tbx_video_clip` WHERE `video_id`=? LIMIT 1', array($video['video_id']));
        $category = $DB->Row('SELECT * FROM `tbx_category` WHERE `category_id`=?', array($video['category_id']));

        if( $clip['clip'][0] == '/' )
        {
            $clip['clip'] = Config::Get('base_url') . $clip['clip'];
        }

        $thumbs = array();
        $th_result = $DB->Query('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=?', array($video['video_id']));
        while( $thumb = $DB->NextRow($th_result) )
        {
            $thumbs[] = $thumb['thumbnail'];
        }
        $DB->Free($th_result);


        $output = array(
            strip_newlines($video['title']),
            strip_newlines($video['description']),
            $video['duration'],
            $category['name'],
            $clip['clip'],
            'http://' . $_SERVER['HTTP_HOST'],
            join(',', $thumbs)
        );

        echo join('|', $output) . "\n";
    }

    $DB->Free($result);
}

function strip_newlines($string)
{
    return trim(str_replace(
        array(
            "\r",
            "\n"
        ),
        ' ',
        $string
    ));
}

?>
