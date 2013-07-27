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

$flag_guest_ratings = Config::Get('flag_guest_ratings');

if( AuthenticateUser::Login() || $flag_guest_ratings )
{
    $username = AuthenticateUser::GetUsername();

    if( $flag_guest_ratings && empty($username) )
    {
        $username = $_SERVER['REMOTE_ADDR'];
    }

    $rating = $_REQUEST['rating'];
    $video_id = $_REQUEST['video_id'];

    if( $rating >= 1 && $rating <= 5 )
    {
        $DB = GetDB();

        if( $DB->QuerySingleColumn('SELECT `allow_ratings` FROM `tbx_video` WHERE `video_id`=?', array($video_id)) == 1 )
        {
            if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_rating` WHERE `username`=? AND `video_id`=?', array($username, $video_id)) == 0 )
            {
                StatsRollover();
                $DB->Update('INSERT INTO `tbx_video_rating` VALUES (?,?,?,?)', array($username, $video_id, $rating, Database_MySQL::Now()));
                $DB->Update('UPDATE `tbx_video_stat` SET ' .
                            '`today_num_ratings`=`today_num_ratings`+1,' .
                            '`today_sum_of_ratings`=`today_sum_of_ratings`+?,' .
                            '`today_avg_rating`=`today_sum_of_ratings`/`today_num_ratings`,' .
                            '`week_num_ratings`=`week_num_ratings`+1,' .
                            '`week_sum_of_ratings`=`week_sum_of_ratings`+?,' .
                            '`week_avg_rating`=`week_sum_of_ratings`/`week_num_ratings`,' .
                            '`month_num_ratings`=`month_num_ratings`+1,' .
                            '`month_sum_of_ratings`=`month_sum_of_ratings`+?,' .
                            '`month_avg_rating`=`month_sum_of_ratings`/`month_num_ratings`,' .
                            '`total_num_ratings`=`total_num_ratings`+1,' .
                            '`total_sum_of_ratings`=`total_sum_of_ratings`+?,' .
                            '`total_avg_rating`=`total_sum_of_ratings`/`total_num_ratings` ' .
                            'WHERE `video_id`=?',
                            array($rating,
                                  $rating,
                                  $rating,
                                  $rating,
                                  $video_id));

                echo _T('Text:Rating recorded');
            }
            else
            {
                echo _T('Validation:You have already rated this video');
            }
        }
        else
        {
            echo _T('Validation:Rating disabled');
        }
    }
    else
    {
        echo _T('Validation:Invalid rating');
    }
}
else
{
    echo _T('Validation:Must be logged in');
}

?>
