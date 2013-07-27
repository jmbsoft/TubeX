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
    $DB = GetDB();
    $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($_REQUEST['video_id']));
    $username = AuthenticateUser::GetUsername();
    $video_id = $_REQUEST['video_id'];
    $comment = $_REQUEST['comment'];
    $max_length = Config::Get('comment_max_length');
    $throttle = Config::Get('comment_throttle_period');

    if( !empty($video) )
    {
        $v = Validator::Create();

        $v->Register($video['allow_comments'], Validator_Type::NOT_EQUALS, _T('Validation:Comments disabled'), COMMENTS_NO);
        $v->Register($_REQUEST['comment'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Comment')));
        $v->Register($_REQUEST['comment'], Validator_Type::LENGTH_LESS_EQ, _T('Validation:Length too long', _T('Label:Comment'), $max_length), $max_length);

        $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_comment` WHERE `video_id`=? AND `username`=? AND `date_commented`>=DATE_SUB(?, INTERVAL ? SECOND)', array($video_id, $username, Database_MySQL::Now(), $throttle)),
                     Validator_Type::IS_ZERO,
                     _T('Validation:Comment throttle', $throttle));

        // Check blacklist
        $_REQUEST['ip_address'] = $_SERVER['REMOTE_ADDR'];
        if( ($match = Blacklist::Match($_REQUEST, Blacklist::ITEM_COMMENT)) !== false )
        {
            $v->SetError(_T('Validation:Blacklisted', $match['match']));
        }

        // Validate CAPTCHA
        if( Config::Get('flag_captcha_on_comment') )
        {
            Captcha::Verify();
        }

        if( !$v->Validate() )
        {
            echo join('<br />', $v->GetErrors());
            return;
        }

        $_REQUEST['username'] = $username;
        $_REQUEST['status'] = $video['allow_comments'] == COMMENTS_APPROVE ? STATUS_PENDING : STATUS_ACTIVE;
        $_REQUEST['date_commented'] = Database_MySQL::Now();

        // Strip HTML tags
        if( Config::Get('flag_comment_strip_tags') )
        {
            $_REQUEST = String::StripTags($_REQUEST);
        }

        DatabaseAdd('tbx_video_comment', $_REQUEST);

        if( $_REQUEST['status'] == STATUS_ACTIVE )
        {
            StatsRollover();

            $DB->Update(
                'UPDATE `tbx_user_stat` SET ' .
                '`today_comments_submitted`=`today_comments_submitted`+1, ' .
                '`week_comments_submitted`=`week_comments_submitted`+1, ' .
                '`month_comments_submitted`=`month_comments_submitted`+1, ' .
                '`total_comments_submitted`=`total_comments_submitted`+1 ' .
                'WHERE `username`=?',
                array(
                    $username
                )
            );

            $DB->Update(
                'UPDATE `tbx_video_stat` SET ' .
                '`today_num_comments`=`today_num_comments`+1,' .
                '`week_num_comments`=`week_num_comments`+1,' .
                '`month_num_comments`=`month_num_comments`+1,' .
                '`total_num_comments`=`total_num_comments`+1 ' .
                'WHERE `video_id`=?',
                array(
                    $video_id
                )
            );

            // Clear first 5 pages of cache
            $t = new Template();
            for( $i = 1; $i <= 5; $i++ )
            {
                $t->ClearCache('video-comments.tpl', $video_id . $i);
                $t->ClearCache('video-comments-iframe.tpl', $video_id . $i);
            }
        }

        echo _T('Text:Comment recorded');
    }
    else
    {
        echo _T('Validation:No such video');
    }
}
else
{
    echo _T('Validation:Must be logged in');
}

?>