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

require_once('includes/global.php');


Request::Setup();


if( AuthenticateUser::Login() )
{
    $username = AuthenticateUser::GetUsername();
    $video_id = $_REQUEST['video_id'];
    $reason_id = $_REQUEST['reason_id'];
    $DB = GetDB();

    if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_featured` WHERE `username`=? AND `video_id`=?', array($username, $video_id)) == 0 )
    {
        StatsRollover();
        
        $DB->Update('INSERT INTO `tbx_video_featured` VALUES (?,?,?,?)', array($video_id, $username, $reason_id, Database_MySQL::Now()));
        $DB->Update('UPDATE `tbx_video_stat` SET ' .
                    '`today_num_featured`=`today_num_featured`+1,' .
                    '`week_num_featured`=`week_num_featured`+1,' .
                    '`month_num_featured`=`month_num_featured`+1,' .
                    '`total_num_featured`=`total_num_featured`+1 ' .
                    'WHERE `video_id`=?',
                    array($video_id));

        echo _T('Text:Feature request recorded');
    }
    else
    {
        echo _T('Validation:You have already featured this video');
    }
}
else
{
    echo _T('Validation:Must be logged in');
}

?>