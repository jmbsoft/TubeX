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

require_once('classes/Config.php');

$clip_id = $_REQUEST['id'];
$clip_url = $_REQUEST['u'];
$user_watching = $_REQUEST['un'];
$user = null;
$limit_exceeded = false;

StatsRollover();
$DB = GetDB();

if( !empty($user_watching) )
{
    $user = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_stat` USING (`username`) JOIN ' .
                     '`tbx_user_level` ON `tbx_user_level`.`user_level_id`=`tbx_user`.`user_level_id` WHERE `tbx_user`.`username`=?', array($user_watching));

    if( !empty($user) )
    {
        $limit_exceeded = !empty($user) &&
                          (
                          ($user['daily_bandwidth_limit'] > 0 && $user['today_bandwidth_used'] > $user['daily_bandwidth_limit']) ||
                          ($user['daily_view_limit'] > 0 && $user['today_videos_watched'] > $user['daily_view_limit'])
                          );

        if( !$limit_exceeded && $clip_url[0] == '/' )
        {
            $clip = $DB->Row('SELECT * FROM `tbx_video_clip` WHERE `clip_id`=?', array($clip_id));

            if( $clip['filesize'] > 0 )
            {
                $DB->Update('UPDATE `tbx_user_stat` SET ' .
                            '`today_bandwidth_used`=`today_bandwidth_used`+?,' .
                            '`week_bandwidth_used`=`week_bandwidth_used`+?,' .
                            '`month_bandwidth_used`=`month_bandwidth_used`+?,' .
                            '`total_bandwidth_used`=`total_bandwidth_used`+? ' .
                            'WHERE `username`=?',
                            array($clip['filesize'],
                                  $clip['filesize'],
                                  $clip['filesize'],
                                  $clip['filesize'],
                                  $user_watching));
            }
        }
    }
}

if( empty($user_watching) || empty($user) )
{
    $ip = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR']));

    $usage = $DB->Row('SELECT * FROM `tbx_guest_usage` WHERE `ip`=?', array($ip));

    if( !empty($usage) )
    {
        $level = $DB->Row('SELECT * FROM `tbx_user_level` WHERE `is_guest`=1');

        if( !empty($level) )
        {
            $limit_exceeded = ($level['daily_bandwidth_limit'] > 0 && $usage['bandwidth'] > $level['daily_bandwidth_limit']) ||
                              ($level['daily_view_limit']> 0 && $usage['watched'] > $level['daily_view_limit']);
        }
    }

    // Handle streaming
    if( isset($_REQUEST['start']) )
    {
        $clip_url .= '?start=' . $_REQUEST['start'];
    }

    if( !$limit_exceeded && $clip_url[0] == '/' )
    {
        $clip = $DB->Row('SELECT * FROM `tbx_video_clip` WHERE `clip_id`=?', array($clip_id));

        if( $clip['filesize'] > 0 )
        {
            if( empty($usage) )
            {
                $DB->Update('INSERT INTO `tbx_guest_usage` VALUES (?,?,1)', array($ip, $clip['filesize']));
            }
            else
            {
                $DB->Update('UPDATE `tbx_guest_usage` SET `bandwidth`=`bandwidth`+? WHERE `ip`=?', array($clip['filesize'], $ip));
            }
        }
    }
}


if( $limit_exceeded )
{
    switch($_GET['pt'])
    {
        case 'wmv':
            $clip_url = Config::Get('template_uri') . '/images/limit.wmv';
            break;

        case 'flv':
            $clip_url = Config::Get('template_uri') . '/images/limit.flv';
            break;

        default:
            $clip_url = Config::Get('template_uri') . '/images/limit.png';
            break;
    }
}

header('Location: ' . $clip_url, true, 301);

?>