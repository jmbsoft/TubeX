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

if( !preg_match('~/admin$~', realpath(dirname(__FILE__))) )
{
    echo "This file must be located in the admin directory of your TubeX installation";
    exit;
}

define('TUBEX_CONTROL_PANEL', true);
require_once('includes/cp-global.php');

$DB = GetDB();

$DB->Update('DELETE FROM `tbx_video_tag`');

$result = $DB->Query('SELECT * FROM `tbx_video` WHERE `status`=? AND `is_private`=?', array(STATUS_ACTIVE, 0));
while( $video = $DB->NextRow($result) )
{
    $video['tags'] = Tags::Format($video['tags']);
    Tags::AddToFrequency($video['tags']);

    $DB->Update('UPDATE `tbx_video` SET `tags`=? WHERE `video_id`=?', array($video['tags'], $video['video_id']));
}
$DB->Free($result);

echo "VIDEO TAGS HAVE BEEN SUCCESSFULLY UPDATED!\n";

?>
