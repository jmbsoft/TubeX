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
    $add = $_REQUEST['add'];
    $DB = GetDB();

    if( $add )
    {
        if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_user_favorite` WHERE `username`=? AND `video_id`=?', array($username, $video_id)) == 0 )
        {
            $DB->Update('INSERT INTO `tbx_user_favorite` VALUES (?,?)', array($username, $video_id));

            if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_favorited` WHERE `video_id`=? AND `username`=?', array($video_id, $username)) == 0 )
            {
                StatsRollover();
                
                $DB->Update('INSERT INTO `tbx_video_favorited` VALUES (?,?,?)', array($video_id, $username, Database_MySQL::Now()));
                $DB->Update('UPDATE `tbx_video_stat` SET ' .
                            '`today_num_favorited`=`today_num_favorited`+1,' .
                            '`week_num_favorited`=`week_num_favorited`+1,' .
                            '`month_num_favorited`=`month_num_favorited`+1,' .
                            '`total_num_favorited`=`total_num_favorited`+1 ' .
                            'WHERE `video_id`=?',
                            array($video_id));
            }

            echo _T('Text:Favorite added');
        }
        else
        {
            echo _T('Text:Favorite exists');
        }
    }
    else
    {
        $DB->Update('DELETE FROM `tbx_user_favorite` WHERE `username`=? AND `video_id`=?', array($username, $video_id));
        echo _T('Text:Favorite removed');
    }
}
else
{
    echo _T('Validation:Must be logged in');
}

?>