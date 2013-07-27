<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

define('TUBEX_CONTROL_PANEL', true);
define('TUBEX_AJAX', true);

require_once('includes/cp-global.php');

// Send initial response headers
header("Content-type: text/javascript; charset: UTF-8");
NoCacheHeaders();

if( Authenticate::Login() )
{
    Execute(Request::Get('r'), 'tbxFunctionMissing');
}
else
{
    Growl::AddError(Authenticate::GetError());
    JSON::Logout();
}


function tbxVideoCommentDelete($comment)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_video_comment` WHERE `comment_id`=?', array($comment['comment_id']));

    if( $comment['status'] == STATUS_ACTIVE )
    {
        UpdateVideoCommentStats($comment, true);
    }

    return true;
}

function tbxVideoCommentApprove($comment)
{
    if( $comment['status'] == STATUS_PENDING )
    {
        $DB = GetDB();
        $schema = GetDBSchema();
        $DB->Update('UPDATE `tbx_video_comment` SET `status`=? WHERE `comment_id`=?', array(STATUS_ACTIVE, $comment['comment_id']));

        UpdateVideoCommentStats($comment);

        return true;
    }

    return false;
}

function tbxVideoCommentReject($comment)
{
    if( $comment['status'] == STATUS_PENDING )
    {
        tbxVideoCommentDelete($comment);
        return true;
    }

    return false;
}

function tbxVideoCommentEmail($video_comment, $xtable, $template = null)
{
    $DB = GetDB();

    if( empty($template) )
    {
        $template = array();
        $template['subject'] = Request::Get('subject');
        $template['message'] = Request::Get('message');
    }

    $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array($video_comment['username']));

    if( empty($user) )
    {
        return;
    }

    $video_comment = array_merge($user, $video_comment);

    $t = new Template();
    $t->Assign('g_config', Config::GetAll());
    $t->AssignByRef('g_video_comment', $video_comment);

    $mailer = new Mailer();
    $mailer->Mail($template, $t, $video_comment['email'], $video_comment['name']);
}

function tbxVideoCustomFieldAdd($phase)
{
    tbxGenericCustomFieldAdd($phase, 'tbx_video', 'tbx_video_custom', 'tbx_video_custom_schema');

    switch($phase)
    {
        case Phase::POST_INSERT:
            // Add column to XML schema
            XML_Schema::AddColumn('tbx_video_custom',
                                  Request::Get('name'),
                                  Request::Get('label'),
                                  Request::Get('validator'),
                                  array('create' => Request::Get('on_submit'), 'edit' => Request::Get('on_edit')));
            break;
    }
}

function tbxVideoCustomFieldEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
        case Phase::PRE_UPDATE:
            tbxGenericCustomFieldEdit($phase);
            break;

        case Phase::POST_UPDATE:
            // Update column in XML schema
            XML_Schema::UpdateColumn('tbx_video_custom',
                                     Request::Get('name'),
                                     Request::Get('label'),
                                     Request::Get('validator'),
                                     array('create' => Request::Get('on_submit'), 'edit' => Request::Get('on_edit')));
            break;
    }
}

function tbxVideoCustomFieldDelete($item)
{
    tbxGenericCustomFieldDelete($item, 'tbx_video_custom');
    return true;
}

function tbxVideoReasonFlaggedShow()
{
    $DB = GetDB();
    $output = array();

    ob_start();
    include('cp-video-reason-flagged.php');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxVideoReasonFlaggedClear()
{


    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_video_flagged` WHERE `video_id`=?', array(Request::Get('video_id')));
    JSON::Success('Reasons have been cleared');
}

function tbxVideoReasonFeaturedShow()
{
    $output = array();

    ob_start();
    include('cp-video-reason-featured.php');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxVideoReasonFeaturedClear()
{


    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_video_featured` WHERE `video_id`=?', array(Request::Get('video_id')));
    JSON::Success('Reasons have been cleared');
}

function tbxVideoFeature($video)
{


    if( !$video['is_featured'] )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_video` SET `is_featured`=1,`date_last_featured`=? WHERE `video_id`=?', array(Database_MySQL::Now(), $video['video_id']));
        return true;
    }

    return false;
}

function tbxVideoUnfeature($video)
{


    if( $video['is_featured'] )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_video` SET `is_featured`=0 WHERE `video_id`=?', array($video['video_id']));
        return true;
    }

    return false;
}

function tbxVideoApprove($video)
{
    if( $video['status'] == STATUS_PENDING )
    {
        $DB = GetDB();
        $schema = GetDBSchema();
        $DB->Update('UPDATE `tbx_video` SET `status`=? WHERE `video_id`=?', array(STATUS_ACTIVE, $video['video_id']));

        if( !$video['is_private'] )
        {
            Tags::AddToFrequency($video['tags']);
        }

        UpdateCategoryStats($video['category_id']);

        $video['status'] = STATUS_ACTIVE;

        if( $video['is_private'] )
        {
            $video['private_id'] = $DB->QuerySingleColumn('SELECT `private_id` FROM `tbx_video_private` WHERE `video_id`=?', array($video['video_id']));
        }

        if( !empty($video['username']) )
        {
            $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array($video['username']));

            if( !empty($user) )
            {
                $user = array_merge($video, $user);
                tbxUserEmail($user, null, 'email-video-approved.tpl', $video);
            }
        }
        return true;
    }

    return false;
}

function tbxVideoReject($video)
{
    if( $video['status'] == STATUS_PENDING )
    {
        $DB = GetDB();
        $schema = GetDBSchema();

        if( !empty($video['username']) )
        {
            $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array($video['username']));

            if( !empty($user) )
            {
                $user = array_merge($video, $user);

                if( isset($_REQUEST['reason_id']) && !empty($_REQUEST['reason_id']) )
                {
                    $user['reject_reason'] = $DB->QuerySingleColumn('SELECT `description` FROM `tbx_reason` WHERE `reason_id`=?', array($_REQUEST['reason_id']));
                }

                tbxUserEmail($user, null, 'email-video-rejected.tpl', $video);
            }
        }

        tbxVideoDelete($video);
        return true;
    }

    return false;
}

function tbxVideoDisable($video)
{
    if( $video['status'] == STATUS_ACTIVE )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_video` SET `status`=? WHERE `video_id`=?', array(STATUS_DISABLED, $video['video_id']));

        UpdateCategoryStats($video['category_id']);

        if( !$video['is_private'] )
        {
            Tags::RemoveFromFrequency($video['tags']);
        }

        return true;
    }

    return false;
}

function tbxVideoEnable($video)
{
    if( $video['status'] == STATUS_DISABLED )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_video` SET `status`=? WHERE `video_id`=?', array(STATUS_ACTIVE, $video['video_id']));

        UpdateCategoryStats($video['category_id']);

        if( !$video['is_private'] )
        {
            Tags::AddToFrequency($video['tags']);
        }

        return true;
    }

    return false;
}

function tbxVideoConvert($video)
{
    $DB = GetDB();

    if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_conversion_queue` WHERE `video_id`=?', array($video['video_id'])) == 0 )
    {
        $video['queued'] = time();
        DatabaseAdd('tbx_conversion_queue', $video);

        return true;
    }

    return false;
}

function tbxVideoThumbnail($video)
{
    $DB = GetDB();

    if( $DB->QueryCount('SELECT COUNT(*) FROM `tbx_thumb_queue` WHERE `video_id`=?', array($video['video_id'])) == 0 )
    {
        $video['queued'] = time();
        DatabaseAdd('tbx_thumb_queue', $video);

        return true;
    }

    return false;
}

function tbxVideoBlacklist($video)
{
    $DB = GetDB();
    // TODO - Update the blacklist
    tbxVideoDelete($video);

    return true;
}

function tbxVideoAdd()
{
    Privileges::Check(Privileges::VIDEOS);


    $DB = GetDB();
    $schema = GetDBSchema();
    $v = Validator::Create();

    $v->RegisterFromXml($schema->el('//table[name="tbx_video"]'));
    $v->RegisterFromXml($schema->el('//table[name="tbx_video_custom"]'));

    if( !String::IsEmpty($_REQUEST['username']) )
    {
        $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `username`=?', array($_REQUEST['username'])) > 0,
                     Validator_Type::IS_TRUE,
                     'The Username entered does not exist');
    }

    try
    {
        $vs = Video_Source_Factory::Create($_REQUEST);
        $vs->PreProcess();
    }
    catch(BaseException $e)
    {
        $v->SetError($e->getMessage());
    }


    if( !$v->Validate() )
    {
        $vs->PostProcessFailure();
        return JSON::Failure(array('message' => 'Video could not be added; please fix the following items', 'errors' => $v->GetErrors()));
    }

    $_REQUEST['date_recorded'] = String::Nullify($_REQUEST['date_recorded']);
    $_REQUEST['username'] = String::Nullify(Request::Get('username'));
    $_REQUEST['status'] = Request::Get(Video_Source::FLAG_CONVERT) ? STATUS_QUEUED : STATUS_ACTIVE;
    $_REQUEST['tags'] = Tags::Format($_REQUEST['tags']);

    $_REQUEST['video_id'] = DatabaseAdd('tbx_video', $_REQUEST);
    DatabaseAdd('tbx_video_custom', $_REQUEST);
    DatabaseAdd('tbx_video_stat', $_REQUEST);

    if( $_REQUEST['is_private'] )
    {
        $_REQUEST['private_id'] = sha1(uniqid(mt_rand(), true));
        DatabaseAdd('tbx_video_private', $_REQUEST);
    }

    // Add to conversion queue
    if( $_REQUEST['status'] == STATUS_QUEUED )
    {
        $_REQUEST['queued'] = time();
        DatabaseAdd('tbx_conversion_queue', $_REQUEST);
    }

    // Add to thumb queue
    if( Request::Get('flag_thumb') )
    {
        $_REQUEST['queued'] = time();
        DatabaseAdd('tbx_thumb_queue', $_REQUEST);
    }

    $vs->PostProcessSuccess($_REQUEST['video_id']);

    if( $_REQUEST['status'] == STATUS_ACTIVE )
    {
        if( !$_REQUEST['is_private'] )
        {
            Tags::AddToFrequency($_REQUEST['tags']);
        }

        $t = new Template();
        $t->ClearCache('categories.tpl');
    }

    UpdateCategoryStats($_REQUEST['category_id']);
    UpdateSponsorStats($_REQUEST['sponsor_id']);

    JSON::Success('Video has been successfully added');

    if( !Config::Get('flag_using_cron') && $_REQUEST['status'] == STATUS_QUEUED )
    {
        ConversionQueue::Start();
    }
}

function tbxVideoEdit()
{


    Privileges::Check(Privileges::VIDEOS);

    $DB = GetDB();
    $schema = GetDBSchema();
    $v = Validator::Create();

    $v->RegisterFromXml($schema->el('//table[name="tbx_video"]'));
    $v->RegisterFromXml($schema->el('//table[name="tbx_video_custom"]'));

    if( !String::IsEmpty($_REQUEST['username']) )
    {
        $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `username`=?', array($_REQUEST['username'])) > 0,
                     Validator_Type::IS_TRUE,
                     'The Username entered does not exist');
    }

    if( !$v->Validate() )
    {
        return JSON::Failure(array('message' => 'Video could not be updated; please fix the following items', 'errors' => $v->GetErrors()));
    }

    $_REQUEST['display_thumbnail'] = empty($_REQUEST['display_thumbnail']) ? null : $_REQUEST['display_thumbnail'];
    $_REQUEST['date_recorded'] = String::Nullify($_REQUEST['date_recorded']);
    $_REQUEST['tags'] = Tags::Format($_REQUEST['tags']);
    $_REQUEST['username'] = String::Nullify(Request::Get('username'));
    $_REQUEST['duration'] = Format::DurationToSeconds($_REQUEST['duration']);

    $original = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($_REQUEST['video_id']));

    // Handle uploaded thumbs, if any
    $dir = new Video_Dir(Video_Dir::DirNameFromId($original['video_id']));
    $thumbs_added = 0;
    $thumb_ids = array();
    Request::FixFiles();
    if( isset($_FILES['thumb_uploads']) )
    {
        foreach( $_FILES['thumb_uploads'] as $upload )
        {
            if( File::Extension($upload['name']) == JPG_EXTENSION && ($imgsize = getimagesize($upload['tmp_name'])) !== false )
            {
                $temp_file = $dir->AddTempFromFile($upload['tmp_name'], JPG_EXTENSION);

                if( Video_Thumbnail::CanResize() )
                {
                    $temp_file = Video_Thumbnail::Resize($temp_file,
                                                         Config::Get('thumb_size'),
                                                         Config::Get('thumb_quality'),
                                                         $dir->GetTempDir());
                }

                $thumb = $dir->AddThumbFromFile($temp_file);

                $thumbs_added++;
                $thumb = str_replace(Config::Get('document_root'), '', $thumb);
                $thumb_ids[] = array('uri' => $thumb,
                                     'id' => DatabaseAdd('tbx_video_thumbnail', array('video_id' => $original['video_id'],
                                                                                      'thumbnail' => $thumb)));
            }
        }

        if( $thumbs_added > 0 )
        {
            $dir->ClearTemp();
            $_REQUEST['num_thumbnails'] = $original['num_thumbnails'] + $thumbs_added;
        }
    }


    // Update base database tables
    $video = DatabaseUpdate('tbx_video', $_REQUEST);
    DatabaseUpdate('tbx_video_custom', $_REQUEST);


    // Handle changes to video clips
    foreach( $_REQUEST['clips'] as $clip_id => $clip )
    {
        DatabaseUpdate('tbx_video_clip', array('video_id' => $video['video_id'],
                                               'clip_id' => $clip_id,
                                               'clip' => $clip['clip']));
    }

    if( $_REQUEST['is_private'] && !$original['is_private'] )
    {
        $_REQUEST['private_id'] = sha1(uniqid(mt_rand(), true));
        DatabaseAdd('tbx_video_private', $_REQUEST);

        if( $original['status'] == STATUS_ACTIVE )
        {
            Tags::RemoveFromFrequency($original['tags']);
        }
    }
    else if( !$_REQUEST['is_private'] )
    {
        if( $original['status'] == STATUS_ACTIVE )
        {
            if( $original['is_private'] )
            {
                Tags::AddToFrequency($_REQUEST['tags']);
            }
            else
            {
                Tags::UpdateFrequency($original['tags'], $_REQUEST['tags']);
            }
        }

        $DB->Update('DELETE FROM `tbx_video_private` WHERE `video_id`=?', array($_REQUEST['video_id']));
    }

    if( $original['status'] == STATUS_ACTIVE )
    {
        $t = new Template();
        $t->ClearCache('categories.tpl');
    }

    UpdateCategoryStats($original['category_id'], $video['category_id']);
    UpdateSponsorStats($original['sponsor_id'], $_REQUEST['sponsor_id']);

    $output = array('id' => $video['video_id'],
                    'message' => 'Video has been successfully updated',
                    'html' => SearchItemHtml('video', $video),
                    'thumbs' => $thumb_ids);

    JSON::Success($output);
}

function tbxVideoBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'sponsor_id':
                    if( $action == BulkEdit::ACTION_SET )
                    {
                        $DB = GetDB();
                        $value = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `name`=?', array($value));
                        $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Sponsor name entered is not valid');
                    }
                    break;

                case 'category_id':
                    $DB = GetDB();
                    $value = $DB->QuerySingleColumn('SELECT `category_id` FROM `tbx_category` WHERE `name`=?', array($value));
                    $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Category name entered is not valid');
                    break;

                case 'username':
                    if( $action == BulkEdit::ACTION_SET )
                    {
                        $DB = GetDB();
                        $value = $DB->QuerySingleColumn('SELECT `username` FROM `tbx_user` WHERE `username`=?', array($value));
                        $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Username entered is not valid');
                    }
                    break;

                case 'duration':
                    if( preg_match('~(\d\d:\d\d:\d\d)~', $value, $matches) )
                    {
                        $value = Format::DurationToSeconds($matches[1]);
                    }
                    break;
            }

            return $value;


        case Phase::POST_UPDATE:
            $field = func_get_arg(1);

            switch($field)
            {
                case 'category_id':
                    UpdateCategoryStats();
                    break;

                case 'sponsor_id':
                    UpdateSponsorStats();
                    break;
            }
            break;
    }
}

function tbxVideoEmail($video, $xtable, $template = null)
{
    $DB = GetDB();

    if( empty($template) )
    {
        $template = array();
        $template['subject'] = Request::Get('subject');
        $template['message'] = Request::Get('message');
    }

    $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array($video['username']));

    if( empty($user) )
    {
        return;
    }

    $video = array_merge($user, $video);

    $t = new Template();
    $t->Assign('g_config', Config::GetAll());
    $t->AssignByRef('g_video', $video);

    $mailer = new Mailer();
    $mailer->Mail($template, $t, $video['email'], $video['name']);
}

function tbxVideoDelete($video)
{
    DeleteVideo($video);

    $t = new Template();
    $t->ClearCache('categories.tpl');

    return true;
}

function tbxThumbnailDelete()
{


    $DB = GetDB();
    $thumb = $DB->Row('SELECT * FROM `tbx_video_thumbnail` WHERE `thumbnail_id`=?', array(Request::Get('thumbnail_id')));
    $output = array('message' => 'Thumbnail has been deleted');

    if( !empty($thumb) )
    {
        $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array($thumb['video_id']));

        // Remove reference from the database
        $DB->Update('DELETE FROM `tbx_video_thumbnail` WHERE `thumbnail_id`=?', array($thumb['thumbnail_id']));


        // Delete the file if locally hosted
        if( $thumb['thumbnail'][0] == '/' )
        {
            @unlink(Config::Get('document_root') . $thumb['thumbnail']);

            // Re-sequence existing thumbnails
            $result = $DB->Query('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=? ORDER BY `thumbnail_id`', array($video['video_id']));
            $counter = 1;
            while( $t = $DB->NextRow($result) )
            {
                $file = Config::Get('document_root') . $t['thumbnail'];

                if( !file_exists($file) )
                {
                    continue;
                }

                $dirname = dirname($file);
                $basename = basename($file);
                $expected = sprintf('%08d.jpg', $counter);

                if( $basename != $expected )
                {
                    rename($file, "$dirname/$expected");
                    $DB->Update('UPDATE `tbx_video_thumbnail` SET `thumbnail`=REPLACE(`thumbnail`, ?, ?) WHERE `thumbnail_id`=?', array($basename, $expected, $t['thumbnail_id']));
                }

                $counter++;
            }
            $DB->Free($result);
        }


        // Update video information
        $updates = array('video_id' => $video['video_id'], 'num_thumbnails' => max(0, $video['num_thumbnails']-1));
        $output['display_thumbnail'] = $video['display_thumbnail'];
        if( $updates['num_thumbnails'] == 0 )
        {
            $updates['display_thumbnail'] = $output['display_thumbnail'] = 0;
        }
        else if( $video['display_thumbnail'] == $thumb['thumbnail_id'] )
        {
            $new = $DB->Row('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=? ORDER BY `thumbnail_id` LIMIT 1', array($video['video_id']));

            $updates['display_thumbnail'] = $output['display_thumbnail'] = empty($new) ? 0 : $new['thumbnail_id'];
        }

        DatabaseUpdate('tbx_video', $updates);
    }

    JSON::Success($output);
}

function tbxFeedTest()
{
    $DB = GetDB();
    $feed = $DB->Row('SELECT * FROM `tbx_video_feed` WHERE `feed_id`=?', array(Request::Get('id')));

    if( $feed )
    {
        $vf = Video_Feed::Create($feed);

        try
        {
            $vf->Test();
            JSON::Success("Feed '" . $feed['name'] . "' appears to be valid!");
        }
        catch(BaseException $e)
        {
            JSON::Failure("Feed '" . $feed['name'] . "' test failed: " . $e->getMessage() . $e->getExtras());
        }
        catch(Exception $e)
        {
            JSON::Failure("Feed '" . $feed['name'] . "' test failed: " . $e->getMessage());
        }
    }
    else
    {
       JSON::Failure('This video feed no longer exists in the database');
    }
}

function tbxFeedRead()
{


    $DB = GetDB();
    $feed = $DB->Row('SELECT * FROM `tbx_video_feed` WHERE `feed_id`=?', array(Request::Get('id')));

    if( $feed )
    {
        $vf = Video_Feed::Create($feed);
        $imported = $vf->Import();

        $output = array();
        $output['message'] = 'Feed has been read successfully; ' . $imported . ' imported';
        $output['feed_id'] = $feed['feed_id'];
        $output['date_last_read'] = date(DATETIME_FRIENDLY, strtotime($DB->QuerySingleColumn('SELECT `date_last_read` FROM `tbx_video_feed` WHERE `feed_id`=?', array($feed['feed_id']))));
        $output['eval'] = file_get_contents('js/cp-video-feed-read.js');

        JSON::Success($output);
    }
    else
    {
       JSON::Failure('This video feed no longer exists in the database');
    }
}

function tbxVideoFeedAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed` WHERE `feed_url`=?', array(Request::Get('feed_url'))),
                         Validator_Type::IS_ZERO,
                         'The Feed URL you have entered already exists');

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed` WHERE `name`=?', array(Request::Get('name'))),
                         Validator_Type::IS_ZERO,
                         'The Feed Name you have entered already exists');
            break;

        case Phase::PRE_INSERT:
            $_REQUEST['username'] = String::Nullify(Request::Get('username'));
            $_REQUEST['sponsor_id'] = String::Nullify(Request::Get('sponsor_id'));
            break;
    }
}

function tbxVideoFeedEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed` WHERE `feed_url`=? AND `feed_id`!=?', array(Request::Get('feed_url'), Request::Get('feed_id'))),
                         Validator_Type::IS_ZERO,
                         'The Feed URL you have entered already exists');

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_feed` WHERE `name`=? AND `feed_id`!=?', array(Request::Get('name'), Request::Get('feed_id'))),
                         Validator_Type::IS_ZERO,
                         'The Feed Name you have entered already exists');
            break;

        case Phase::PRE_UPDATE:
            $_REQUEST['username'] = String::Nullify(Request::Get('username'));
            $_REQUEST['sponsor_id'] = String::Nullify(Request::Get('sponsor_id'));
            break;
    }
}

function tbxVideoFeedBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'sponsor_id':
                    if( $action == BulkEdit::ACTION_SET )
                    {
                        $DB = GetDB();
                        $value = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `name`=?', array($value));
                        $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Sponsor name entered is not valid');
                    }
                    break;

                case 'category_id':
                    $DB = GetDB();
                    $value = $DB->QuerySingleColumn('SELECT `category_id` FROM `tbx_category` WHERE `name`=?', array($value));
                    $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Category name entered is not valid');
                    break;

                case 'username':
                    if( $action == BulkEdit::ACTION_SET )
                    {
                        $DB = GetDB();
                        $value = $DB->QuerySingleColumn('SELECT `username` FROM `tbx_user` WHERE `username`=?', array($value));
                        $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Category name entered is not valid');
                    }
                    break;
            }

            return $value;
    }
}

function tbxVideoFeedDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_video_feed` WHERE `feed_id`=?', array($item['feed_id']));
    $DB->Update('DELETE FROM `tbx_video_feed_history` WHERE `feed_id`=?', array($item['feed_id']));

    return true;
}

function tbxSearchTermAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_search_term` WHERE `term`=?', array(Request::Get('term'))),
                         Validator_Type::IS_ZERO,
                         'This search term already exists');
            break;
    }
}

function tbxSearchTermEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_search_term` WHERE `term`=? AND `term_id`!=?', array(Request::Get('term'), Request::Get('term_id'))),
                         Validator_Type::IS_ZERO,
                         'This search term already exists');
            break;
    }
}

function tbxSearchTermDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_search_term` WHERE `term_id`=?', array($item['term_id']));

    return true;
}

function tbxReasonAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_reason` WHERE `short_name`=? AND `type`=?', array(Request::Get('short_name'), Request::Get('type'))),
                         Validator_Type::IS_ZERO,
                         'This reason already exists');
            break;
    }
}

function tbxReasonEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_reason` WHERE `short_name`=? AND `type`=? AND `reason_id`!=?', array(Request::Get('short_name'), Request::Get('type'), Request::Get('reason_id'))),
                         Validator_Type::IS_ZERO,
                         'This reason already exists');
            break;
    }
}

function tbxReasonDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_reason` WHERE `reason_id`=?', array($item['reason_id']));
    $DB->Update('DELETE FROM `tbx_video_flagged` WHERE `reason_id`=?', array($item['reason_id']));
    $DB->Update('DELETE FROM `tbx_video_featured` WHERE `reason_id`=?', array($item['reason_id']));

    return true;
}

function tbxUserEmail($user, $xtable = null, $template = null, $video = null)
{
    $DB = GetDB();

    if( empty($template) )
    {
        $template = array();
        $template['subject'] = Request::Get('subject');
        $template['message'] = Request::Get('message');
    }

    if( !empty($xtable) )
    {
        $user = MergeTables($xtable, $user);
    }

    $t = new Template();
    $t->Assign('g_config', Config::GetAll());
    $t->AssignByRef('g_user', $user);

    if( !empty($video) )
    {
        $t->AssignByRef('g_video', $video);
    }

    $mailer = new Mailer();
    $mailer->Mail($template, $t, $user['email'], $user['name']);
}

function tbxUserApprove($user)
{
    if( $user['status'] == STATUS_PENDING )
    {
        $DB = GetDB();
        $schema = GetDBSchema();
        $DB->Update('UPDATE `tbx_user` SET `status`=? WHERE `username`=?', array(STATUS_ACTIVE, $user['username']));
        tbxUserEmail($user, $schema->el('//table[name="tbx_user"]'), 'email-user-approved.tpl');
        return true;
    }

    return false;
}

function tbxUserReject($user)
{
    if( $user['status'] == STATUS_PENDING )
    {
        $DB = GetDB();
        $schema = GetDBSchema();

        if( isset($_REQUEST['reason_id']) && !empty($_REQUEST['reason_id']) )
        {
            $user['reject_reason'] = $DB->QuerySingleColumn('SELECT `description` FROM `tbx_reason` WHERE `reason_id`=?', array($_REQUEST['reason_id']));
        }

        tbxUserEmail($user, $schema->el('//table[name="tbx_user"]'), 'email-user-rejected.tpl');
        tbxUserDelete($user);
        return true;
    }

    return false;
}

function tbxUserDisable($user)
{
    if( $user['status'] == STATUS_ACTIVE )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_user` SET `status`=? WHERE `username`=?', array(STATUS_DISABLED, $user['username']));
        return true;
    }

    return false;
}

function tbxUserEnable($user)
{
    if( $user['status'] == STATUS_DISABLED )
    {
        $DB = GetDB();
        $DB->Update('UPDATE `tbx_user` SET `status`=? WHERE `username`=?', array(STATUS_ACTIVE, $user['username']));
        return true;
    }

    return false;
}

function tbxUserBlacklist($user)
{
    $DB = GetDB();
    // TODO - Update the blacklist
    //tbxUserDelete($user);

    return true;
}

function tbxUserAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register(Request::Get('password'), Validator_Type::NOT_EMPTY, 'The Password field is required');
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `username`=?', array(Request::Get('username'))),
                         Validator_Type::IS_ZERO,
                         'This Username is already taken');

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `email`=?', array(Request::Get('email'))),
                         Validator_Type::IS_ZERO,
                         'This E-mail Address is already in use by another account');

            Uploads::ProcessNew('jpg,gif,png');
            $upload = Uploads::Get('avatar_file');

            if( !empty($upload) )
            {
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);

                $imagesize = @getimagesize($upload['path']);
                $v->Register($imagesize, Validator_Type::NOT_FALSE, 'The uploaded file is not a valid image');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_INSERT:
            $_REQUEST['password'] = sha1(Request::Get('password'));
            $_REQUEST['date_birth'] = String::Nullify(Request::Get('date_birth'));

            // Handle avatar file upload, if any
            $upload = Uploads::Get('avatar_file');
            if( !empty($upload) )
            {
                $_REQUEST['avatar_id'] = $upload['upload_id'];
            }
            break;

        case Phase::POST_INSERT:
            DatabaseAdd('tbx_user_stat', $_REQUEST);

            if( Request::Get('flag_send_email') )
            {
                $schema = GetDBSchema();
                tbxUserEmail($_REQUEST, $schema->el('//table[name="tbx_user"]'), 'email-user-added.tpl');
            }
            break;
    }
}

function tbxUserEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `email`=? AND `username`!=?', array(Request::Get('email'), Request::Get('username'))),
                         Validator_Type::IS_ZERO,
                         'This E-mail Address is already in use by another account');

            Uploads::ProcessNew('jpg,gif,png');
            $upload = Uploads::Get('avatar_file');

            if( !empty($upload) )
            {
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);

                $imagesize = @getimagesize($upload['path']);
                $v->Register($imagesize, Validator_Type::NOT_FALSE, 'The uploaded file is not a valid image');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_UPDATE:
            $_REQUEST['date_birth'] = String::Nullify(Request::Get('date_birth'));

            $DB = GetDB();
            $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array(Request::Get('username')));
            $upload = Uploads::Get('avatar_file');

            if( !empty($upload) )
            {
                if( !empty($user['avatar_id']) )
                {
                    Uploads::RemoveExisting($user['avatar_id']);
                }

                $_REQUEST['avatar_id'] = $upload['upload_id'];
            }
            else if( Request::Get('remove_avatar') )
            {
                Uploads::RemoveExisting($user['avatar_id']);
                $_REQUEST['avatar_id'] = null;
            }

            if( !String::IsEmpty($_REQUEST['password']) )
            {
                $_REQUEST['password'] = sha1($_REQUEST['password']);
            }
            else
            {
                unset($_REQUEST['password']);
            }
            break;
    }
}

function tbxUserBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'user_level_id':
                    $DB = GetDB();
                    $value = $DB->QuerySingleColumn('SELECT `user_level_id` FROM `tbx_user_level` WHERE `name`=?', array($value));
                    $v->Register(empty($value), Validator_Type::IS_FALSE, 'The User Level name entered is not valid');
                    break;
            }

            return $value;
    }
}

function tbxUserDelete($user)
{
    $DB = GetDB();

    $DB->Update('DELETE FROM `tbx_user` WHERE `username`=?', array($user['username']));
    $DB->Update('DELETE FROM `tbx_user_stat` WHERE `username`=?', array($user['username']));
    $DB->Update('DELETE FROM `tbx_user_custom` WHERE `username`=?', array($user['username']));
    $DB->Update('DELETE FROM `tbx_user_favorite` WHERE `username`=?', array($user['username']));
    $DB->Update('DELETE FROM `tbx_user_session` WHERE `username`=?', array($user['username']));

    if( !empty($user['avatar_id']) )
    {
        Uploads::RemoveExisting($user['avatar_id']);
    }

    // Delete all of the user's videos
    $result = $DB->Query('SELECT * FROM `tbx_video` WHERE `username`=?', array($user['username']));
    while( $video = $DB->NextRow($result) )
    {
        DeleteVideo($video);
    }
    $DB->Free($result);

    return true;
}

function tbxUserCustomFieldAdd($phase)
{
    tbxGenericCustomFieldAdd($phase, 'tbx_user', 'tbx_user_custom', 'tbx_user_custom_schema');

    switch($phase)
    {
        case Phase::POST_INSERT:
            // Add column to XML schema
            XML_Schema::AddColumn('tbx_user_custom',
                                  Request::Get('name'),
                                  Request::Get('label'),
                                  Request::Get('validator'),
                                  array('create' => Request::Get('on_submit'), 'edit' => Request::Get('on_edit')));
            break;
    }
}

function tbxUserCustomFieldEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
        case Phase::PRE_UPDATE:
            tbxGenericCustomFieldEdit($phase);
            break;

        case Phase::POST_UPDATE:
            // Update column in XML schema
            XML_Schema::UpdateColumn('tbx_user_custom',
                                     Request::Get('name'),
                                     Request::Get('label'),
                                     Request::Get('validator'),
                                     array('create' => Request::Get('on_submit'), 'edit' => Request::Get('on_edit')));
            break;
    }
}

function tbxUserCustomFieldDelete($item)
{
    tbxGenericCustomFieldDelete($item, 'tbx_user_custom');
    return true;
}

function tbxSponsorCustomFieldAdd($phase)
{
    tbxGenericCustomFieldAdd($phase, 'tbx_sponsor', 'tbx_sponsor_custom', 'tbx_sponsor_custom_schema');

    switch($phase)
    {
        case Phase::POST_INSERT:
            // Add column to XML schema
            XML_Schema::AddColumn('tbx_sponsor_custom', Request::Get('name'), Request::Get('label'), Request::Get('validator'));
            break;
    }
}

function tbxSponsorCustomFieldEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
        case Phase::PRE_UPDATE:
            tbxGenericCustomFieldEdit($phase);
            break;

        case Phase::POST_UPDATE:
            // Update column in XML schema
            XML_Schema::UpdateColumn('tbx_sponsor_custom', Request::Get('name'), Request::Get('label'), Request::Get('validator'));
            break;
    }
}

function tbxSponsorCustomFieldDelete($item)
{
    tbxGenericCustomFieldDelete($item, 'tbx_sponsor_custom');
    return true;
}

function tbxSponsorAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_sponsor` WHERE `name`=?', array(Request::Get('name'))),
                         Validator_Type::IS_ZERO,
                         'A sponsor with this Name already exists');
            break;
    }
}

function tbxSponsorEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_sponsor` WHERE `name`=? AND `sponsor_id`!=?', array(Request::Get('name'), Request::Get('sponsor_id'))),
                         Validator_Type::IS_ZERO,
                         'A sponsor with this Name already exists');
            break;
    }
}

function tbxSponsorDelete($sponsor)
{
    $DB = GetDB();

    $DB->Update('DELETE FROM `tbx_sponsor` WHERE `sponsor_id`=?', array($sponsor['sponsor_id']));
    $DB->Update('DELETE FROM `tbx_sponsor_custom` WHERE `sponsor_id`=?', array($sponsor['sponsor_id']));
    $DB->Update('DELETE FROM `tbx_video_feed` WHERE `sponsor_id`=?', array($sponsor['sponsor_id']));

    $result = $DB->Query('SELECT * FROM `tbx_banner` WHERE `sponsor_id`=?', array($sponsor['sponsor_id']));
    while( $banner = $DB->NextRow($result) )
    {
        tbxBannerDelete($banner);
    }
    $DB->Free($result);


    $result = $DB->Query('SELECT * FROM `tbx_video` WHERE `sponsor_id`=?', array($sponsor['sponsor_id']));
    while( $video = $DB->NextRow($result) )
    {
        tbxVideoDelete($video);
    }
    $DB->Free($result);

    return true;
}

function tbxCategoryCustomFieldAdd($phase)
{
    tbxGenericCustomFieldAdd($phase, 'tbx_category', 'tbx_category_custom', 'tbx_category_custom_schema');

    switch($phase)
    {
        case Phase::POST_INSERT:
            // Add column to XML schema
            XML_Schema::AddColumn('tbx_category_custom', Request::Get('name'), Request::Get('label'), Request::Get('validator'));
            break;
    }
}

function tbxCategoryCustomFieldEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
        case Phase::PRE_UPDATE:
            tbxGenericCustomFieldEdit($phase);
            break;

        case Phase::POST_UPDATE:
            // Update column in XML schema
            XML_Schema::UpdateColumn('tbx_category_custom', Request::Get('name'), Request::Get('label'), Request::Get('validator'));
            break;
    }
}

function tbxCategoryCustomFieldDelete($item)
{
    tbxGenericCustomFieldDelete($item, 'tbx_category_custom');
    return true;
}

function tbxCategoryAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_category` WHERE `name`=?', array(Request::Get('name'))),
                         Validator_Type::IS_ZERO,
                         'A category with this Name already exists');

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_category` WHERE `url_name`=?', array(Request::Get('url_name'))),
                         Validator_Type::IS_ZERO,
                         'A category with this URL Name already exists');

            Uploads::ProcessNew('jpg,gif,png');
            $upload = Uploads::Get('image_file');

            if( !empty($upload) )
            {
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);

                $imagesize = @getimagesize($upload['path']);
                $v->Register($imagesize, Validator_Type::NOT_FALSE, 'The uploaded file is not a valid image');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_INSERT:
            // Handle image file upload, if any
            $upload = Uploads::Get('image_file');
            if( !empty($upload) )
            {
                $_REQUEST['image_id'] = $upload['upload_id'];
            }
            break;

        case Phase::POST_INSERT:
            $t = new Template();
            $t->ClearCache('categories.tpl');
            break;
    }
}

function tbxCategoryEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_category` WHERE `name`=? AND `category_id`!=?', array(Request::Get('name'), Request::Get('category_id'))),
                         Validator_Type::IS_ZERO,
                         'A category with this Name already exists');

            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_category` WHERE `url_name`=? AND `category_id`!=?', array(Request::Get('url_name'), Request::Get('category_id'))),
                         Validator_Type::IS_ZERO,
                         'A category with this URL Name already exists');

            Uploads::ProcessNew('jpg,gif,png');
            $upload = Uploads::Get('image_file');

            if( !empty($upload) )
            {
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);

                $imagesize = @getimagesize($upload['path']);
                $v->Register($imagesize, Validator_Type::NOT_FALSE, 'The uploaded file is not a valid image');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_UPDATE:
            $DB = GetDB();
            $category = $DB->Row('SELECT * FROM `tbx_category` WHERE `category_id`=?', array(Request::Get('category_id')));
            $upload = Uploads::Get('image_file');

            if( !empty($upload) )
            {
                if( !empty($category['image_id']) )
                {
                    Uploads::RemoveExisting($category['image_id']);
                }

                $_REQUEST['image_id'] = $upload['upload_id'];
            }
            else if( Request::Get('remove_image') )
            {
                Uploads::RemoveExisting($category['image_id']);
                $_REQUEST['image_id'] = null;
            }
            break;

        case Phase::POST_UPDATE:
            $t = new Template();
            $t->ClearCache('categories.tpl');
            break;
    }
}

function tbxCategoryDelete($category)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_category` WHERE `category_id`=?', array($category['category_id']));
    $DB->Update('DELETE FROM `tbx_category_custom` WHERE `category_id`=?', array($category['category_id']));

    // Assign new default category to video feeds
    $category_id = $DB->QuerySingleColumn('SELECT MIN(`category_id`) FROM `tbx_category`');

    if( empty($category_id) )
    {
        $DB->Update('DELETE FROM `tbx_video_feed` WHERE `category_id`=?', array($category['category_id']));
    }
    else
    {
        $DB->Update('UPDATE `tbx_video_feed` SET `category_id`=? WHERE `category_id`=?', array($category_id, $category['category_id']));
    }

    $result = $DB->Query('SELECT * FROM `tbx_video` WHERE `category_id`=?', array($category['category_id']));
    while( $video = $DB->NextRow($result) )
    {
        tbxVideoDelete($video);
    }
    $DB->Free($result);

    $t = new Template();
    $t->ClearCache('categories.tpl');

    return true;
}

function tbxUserLevelAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_user_level` WHERE `name`=?', array(Request::Get('name'))),
                         Validator_Type::IS_ZERO,
                         'A user level with this name already exists');

            $_REQUEST['daily_bandwidth_limit'] = Format::StringToBytes(Request::Get('daily_bandwidth_limit'));
            break;

        case Phase::POST_INSERT:
            $DB = GetDB();
            if( Request::Get('is_default') )
            {
                $default_user_level = $DB->Row('SELECT * FROM `tbx_user_level` WHERE `is_default`=?', array(1));
                $DB->Update('UPDATE `tbx_user_level` SET `is_default`=0 WHERE `user_level_id`=?', array($default_user_level['user_level_id']));
                $DB->Update('UPDATE `tbx_user` SET `user_level_id`=? WHERE `user_level_id`=?', array($_REQUEST['user_level_id'], $default_user_level['user_level_id']));
            }

            if( Request::Get('is_guest') )
            {
                $guest_user_level = $DB->Row('SELECT * FROM `tbx_user_level` WHERE `is_guest`=?', array(1));
                $DB->Update('UPDATE `tbx_user_level` SET `is_guest`=0 WHERE `user_level_id`=?', array($guest_user_level['user_level_id']));
                $DB->Update('UPDATE `tbx_user` SET `user_level_id`=? WHERE `user_level_id`=?', array($_REQUEST['user_level_id'], $guest_user_level['user_level_id']));
            }
            break;
    }
}

function tbxUserLevelEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register($DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_user_level` WHERE `name`=? AND `user_level_id`!=?', array(Request::Get('name'), Request::Get('user_level_id'))),
                         Validator_Type::IS_ZERO,
                         'A user level with this name already exists');

            $_REQUEST['daily_bandwidth_limit'] = Format::StringToBytes(Request::Get('daily_bandwidth_limit'));
            break;

        case Phase::PRE_UPDATE:
            $DB = GetDB();
            if( Request::Get('is_default') )
            {
                $default_user_level = $DB->Row('SELECT * FROM `tbx_user_level` WHERE `is_default`=?', array(1));

                if( $default_user_level['user_level_id'] != $_REQUEST['user_level_id'] )
                {
                    $DB->Update('UPDATE `tbx_user_level` SET `is_default`=0 WHERE `user_level_id`=?', array($default_user_level['user_level_id']));
                    $DB->Update('UPDATE `tbx_user` SET `user_level_id`=? WHERE `user_level_id`=?', array($_REQUEST['user_level_id'], $default_user_level['user_level_id']));
                }
            }

            if( Request::Get('is_guest') )
            {
                $guest_user_level = $DB->Row('SELECT * FROM `tbx_user_level` WHERE `is_guest`=?', array(1));

                if( $guest_user_level['user_level_id'] != $_REQUEST['user_level_id'] )
                {
                    $DB->Update('UPDATE `tbx_user_level` SET `is_guest`=0 WHERE `user_level_id`=?', array($guest_user_level['user_level_id']));
                    $DB->Update('UPDATE `tbx_user` SET `user_level_id`=? WHERE `user_level_id`=?', array($_REQUEST['user_level_id'], $guest_user_level['user_level_id']));
                }
            }
            break;
    }
}

function tbxUserLevelBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'daily_bandwidth_limit':
                    $value = Format::StringToBytes($value);
                    break;
            }

            return $value;
    }
}

function tbxUserLevelDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_user_level` WHERE `user_level_id`=?', array($item['user_level_id']));
    $DB->Update('UPDATE `tbx_user` SET `user_level_id`=NULL WHERE `user_level_id`=?', array($item['user_level_id']));
    return true;
}

function tbxBannerAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            Uploads::ProcessNew();
            $upload = Uploads::Get('upload_file');

            if( !empty($upload) )
            {
                $v = Validator::Get();
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);
                $v->Register(stripos(Request::Get('banner_html'), '{$upload_file}'),
                             Validator_Type::NOT_FALSE,
                             'The Banner HTML must contain {$upload_file} where you want the URL of the upload file placed');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_INSERT:
            $upload = Uploads::Get('upload_file');

            if( !empty($upload) )
            {
                $_REQUEST['upload_id'] = $upload['upload_id'];
                $_REQUEST['banner_html'] = str_replace('{$upload_file}', $upload['uri'], $_REQUEST['banner_html']);
            }

            $_REQUEST['tags'] = Tags::Format(Request::Get('tags'));
            $_REQUEST['sponsor_id'] = String::Nullify($_REQUEST['sponsor_id']);

            if( !empty($_REQUEST['sponsor_id']) )
            {
                $DB = GetDB();
                $sponsor = $DB->Row('SELECT * FROM `tbx_sponsor` WHERE `sponsor_id`=?', array($_REQUEST['sponsor_id']));

                if( !empty($sponsor) )
                {
                    $_REQUEST['banner_html'] = str_replace('{$sponsor_url}', $sponsor['url'], $_REQUEST['banner_html']);
                }
            }
            break;
    }
}

function tbxBannerEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            Uploads::ProcessNew();
            $upload = Uploads::Get('upload_file');

            if( !empty($upload) )
            {
                $v = Validator::Get();
                $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);
                $v->Register(stripos(Request::Get('banner_html'), '{$upload_file}'),
                             Validator_Type::NOT_FALSE,
                             'The Banner HTML must contain {$upload_file} where you want the URL of the upload file placed');
            }
            break;

        case Phase::VALIDATION_FAILED:
            Uploads::RemoveCurrent();
            break;

        case Phase::PRE_UPDATE:
            $DB = GetDB();
            $banner = $DB->Row('SELECT * FROM `tbx_banner` WHERE `banner_id`=?', array($_REQUEST['banner_id']));
            $upload = Uploads::Get('upload_file');

            if( !empty($upload) )
            {
                if( !empty($banner['upload_id']) )
                {
                    Uploads::RemoveExisting($banner['upload_id']);
                }

                $_REQUEST['upload_id'] = $upload['upload_id'];
                $_REQUEST['banner_html'] = str_replace('{$upload_file}', $upload['uri'], $_REQUEST['banner_html']);
            }

            $_REQUEST['sponsor_id'] = String::Nullify($_REQUEST['sponsor_id']);

            if( !empty($_REQUEST['sponsor_id']) )
            {
                $DB = GetDB();
                $sponsor = $DB->Row('SELECT * FROM `tbx_sponsor` WHERE `sponsor_id`=?', array($_REQUEST['sponsor_id']));

                if( !empty($sponsor) )
                {
                    $_REQUEST['banner_html'] = str_replace('{$sponsor_url}', $sponsor['url'], $_REQUEST['banner_html']);
                }
            }
            break;
    }
}

function tbxBannerBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'sponsor_id':
                    $DB = GetDB();
                    $value = $DB->QuerySingleColumn('SELECT `sponsor_id` FROM `tbx_sponsor` WHERE `name`=?', array($value));
                    $v->Register(empty($value), Validator_Type::IS_FALSE, 'The Sponsor name entered is not valid');
                    break;
            }

            return $value;
    }
}

function tbxBannerDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_banner` WHERE `banner_id`=?', array($item['banner_id']));

    if( isset($item['upload_id']) )
    {
        Uploads::RemoveExisting($item['upload_id']);
    }

    return true;
}

function tbxBlacklistItemAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            // Prevent duplicates
            $DB = GetDB();
            $v = Validator::Get();
            $v->Register($DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_blacklist` WHERE `value`=? AND `type`=?', array(Request::Get('value'), Request::Get('type'))),
                         Validator_Type::IS_ZERO,
                         'The blacklist item you are submitting already exists');
            break;
    }
}

function tbxBlacklistItemEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            // Prevent duplicates
            $DB = GetDB();
            $v = Validator::Get();
            $v->Register($DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_blacklist` WHERE `value`=? AND `type`=? AND `blacklist_id`!=?',
                                                array(Request::Get('value'), Request::Get('type'), Request::Get('blacklist_id'))),
                         Validator_Type::IS_ZERO,
                         'The blacklist item you are submitting already exists');
            break;
    }
}

function tbxBlacklistItemDelete($item)
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_blacklist` WHERE `blacklist_id`=?', array($item['blacklist_id']));
    return true;
}

function tbxAdministratorAdd($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $v = Validator::Get();
            $DB = GetDB();
            $v->Register(Request::Get('password'), Validator_Type::NOT_EMPTY, 'The Password field is required');
            $v->Register($DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_administrator` WHERE `username`=?', array(Request::Get('username'))),
                         Validator_Type::IS_ZERO,
                         'An administrator account already exists with this username');
            break;

        case Phase::PRE_INSERT:
            $_REQUEST['password'] = sha1($_REQUEST['password']);
            $_REQUEST['privileges'] = Privileges::Generate($_REQUEST);
            break;
    }
}

function tbxAdministratorEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            if( !String::IsEmpty($_REQUEST['password']) )
            {
                $_REQUEST['password'] = sha1($_REQUEST['password']);
            }
            else
            {
                unset($_REQUEST['password']);
            }

            $_REQUEST['privileges'] = Privileges::Generate($_REQUEST);
            break;
    }
}

function tbxAdministratorBulkEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_UPDATE:
            $field = func_get_arg(1);
            $value = func_get_arg(2);
            $action = func_get_arg(3);
            $v = Validator::Create();

            switch( $field )
            {
                case 'type':
                    $v->Register($value, Validator_Type::REGEX_MATCH, 'The Account Type must be either Editor or Superuser', '~^Editor|Superuser$~');
                    break;
            }

            return $value;
    }
}

function tbxAdministratorDelete($item)
{
    // Do not allow user to delete their own account
    if( $item['username'] == Authenticate::GetUsername() )
    {
        return false;
    }

    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_administrator` WHERE `username`=?', array($item['username']));

    return true;
}

function tbxAdministratorEmail($administrator, $xtable, $template = null)
{
    $DB = GetDB();

    if( empty($template) )
    {
        $template = array();
        $template['subject'] = Request::Get('subject');
        $template['message'] = Request::Get('message');
    }

    $t = new Template();
    $t->Assign('g_config', Config::GetAll());
    $t->AssignByRef('g_administrator', $administrator);

    $mailer = new Mailer();
    $mailer->Mail($template, $t, $administrator['email'], $administrator['name']);
}


function tbxThumbQueueStats()
{
    include('cp-thumb-queue-stats.php');
}

function tbxThumbQueueClear()
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_thumb_queue`');
    JSON::Success('The thumbnail queue has been cleared');
}

function tbxThumbQueueStop()
{
    ThumbQueue::Stop();
    JSON::Success('The thumbnail queue has been flagged to stop.  The current video being thumbnailed will be finished, ' .
                  'therefore it can take several minutes for the thumbnail queue to actually stop running.');
}

function tbxThumbQueueStart()
{
    ThumbQueue::Start();
    JSON::Success('The thumbnail queue has been started');
}

function tbxThumbQueueShow()
{
    $output = array();

    ob_start();
    include('cp-thumb-queue.php');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxConvesionLogView()
{
    $log = Video_Dir::DirNameFromId($_REQUEST['video_id']) . '/convert.log';

    if( file_exists($log) )
    {
        JSON::Success(array('html' => '<xmp class="conversion-log">' . trim(file_get_contents($log)) . '</xmp>'));
    }
    else
    {
        JSON::Success('No conversion log exists for this video');
    }
}

function tbxConversionQueueClear()
{
    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_conversion_queue`');
    JSON::Success('The conversion queue has been cleared');
}

function tbxConversionQueueStop()
{
    ConversionQueue::Stop();
    JSON::Success('The conversion queue has been flagged to stop.  It can take several minutes for the conversion queue to actually stop running.');
}

function tbxConversionQueueStart()
{
    ConversionQueue::Start();
    JSON::Success('The conversion queue has been started');
}

function tbxConversionQueueStats()
{
    include('cp-conversion-queue-stats.php');
}

function tbxConversionQueueShow()
{
    $output = array();

    ob_start();
    include('cp-conversion-queue.php');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxGenericCustomFieldAdd($phase, $base_table, $custom_table, $schema_table)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $DB = GetDB();
            $v = Validator::Create();

            $v->Register(is_writeable(INCLUDES_DIR . '/database.xml'), Validator_Type::IS_TRUE, 'The includes/database.xml file is not writeable; change permissions to 666');
            $v->Register(Request::Get('name'), Validator_Type::NOT_EMPTY, 'The Name field is required');
            $v->Register(Request::Get('name'), Validator_Type::REGEX_MATCH, 'The Name field can contain only English letter, number, and underscore characters', '~^[a-z0-9_]+$~i');
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM # WHERE `name`=?', array($schema_table, Request::Get('name'))),
                         Validator_Type::IS_ZERO,
                         'A custom field with this Name already exists');

            $schema = GetDBSchema();
            $xcolumn = $schema->el('//table[name="' . $base_table . '"]/columns/column[name="' . Request::Get('name') . '"]');
            $v->Register(empty($xcolumn), Validator_Type::IS_TRUE, 'The default table already has a field with this name');
            break;

        case Phase::PRE_INSERT:
            $validators = array('type' => array(), 'message' => array(), 'extras' => array());
            foreach( $_REQUEST['validator']['type'] as $i => $type )
            {
                if( $type != Validator_Type::NONE )
                {
                    $validators['type'][] = $_REQUEST['validator']['type'][$i];
                    $validators['message'][] = $_REQUEST['validator']['message'][$i];
                    $validators['extras'][] = $_REQUEST['validator']['extras'][$i];
                }
            }

            $_REQUEST['validators'] = serialize($validators);

            if( $_REQUEST['type'] == Form_Field::CHECKBOX )
            {
                $_REQUEST['tag_attributes'] = Form_Field::ParseAttributes($_REQUEST['tag_attributes'], 'value', true);
            }
            break;

        case Phase::POST_INSERT:
            // Add column to the database
            $DB = GetDB();
            $DB->Update('ALTER TABLE # ADD COLUMN # TEXT', array($custom_table, Request::Get('name')));
            break;
    }
}

function tbxGenericCustomFieldEdit($phase)
{
    switch($phase)
    {
        case Phase::PRE_VALIDATE:
            $v = Validator::Create();
            $v->Register(is_writeable(INCLUDES_DIR . '/database.xml'), Validator_Type::IS_TRUE, 'The includes/database.xml file is not writeable; change permissions to 666');
            break;

        case Phase::PRE_UPDATE:
            $validators = array('type' => array(), 'message' => array(), 'extras' => array());
            foreach( $_REQUEST['validator']['type'] as $i => $type )
            {
                if( $type != Validator_Type::NONE )
                {
                    $validators['type'][] = $_REQUEST['validator']['type'][$i];
                    $validators['message'][] = $_REQUEST['validator']['message'][$i];
                    $validators['extras'][] = $_REQUEST['validator']['extras'][$i];
                }
            }

            $_REQUEST['validators'] = serialize($validators);

            if( $_REQUEST['type'] == Form_Field::CHECKBOX )
            {
                $_REQUEST['tag_attributes'] = Form_Field::ParseAttributes($_REQUEST['tag_attributes'], 'value', true);
            }
            break;

        case Phase::POST_UPDATE:
            break;
    }
}

function tbxGenericCustomFieldDelete($item, $custom_table)
{
    // Delete column from XML schema
    XML_Schema::DeleteColumn($custom_table, $item['name']);

    // Remove column from the database
    $DB = GetDB();
    $DB->Update('DELETE FROM # WHERE `name`=?', array($custom_table.'_schema', $item['name']));
    $DB->Update('ALTER TABLE # DROP COLUMN #', array($custom_table, $item['name']));
}

function tbxGlobalSettingsShow()
{
    Privileges::CheckSuper();
    $si = ServerInfo::Get(true);

    $output = array();

    ob_start();
    include('cp-global-settings.php');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxGlobalSettingsSave()
{


    Privileges::CheckSuper();

    $si = ServerInfo::GetCached();

    $required = array('site_name' => 'Site Name',
                      'meta_description' => 'Meta Description',
                      'meta_keywords' => 'Meta Keywords',
                      'document_root' => 'Document Root',
                      'base_url' => 'TubeX URL',
                      'cookie_domain' => 'Cookie Domain',
                      'cookie_path' => 'Cookie Path',
                      'email_address' => 'E-mail Address',
                      'email_name' => 'E-mail Name',
                      'date_format' => 'Date Format',
                      'time_format' => 'Time Format',
                      'dec_point' => 'Decimal Point',
                      'thousands_sep' => 'Thousands Separator',
                      'video_extensions' => 'File Extensions');


    switch( Request::Get('mailer') )
    {
        case Mailer::SMTP:
            $required['smtp_hostname'] = 'SMTP Hostname';
            $required['smtp_port'] = 'SMTP Port';
            break;

        case Mailer::SENDMAIL:
            $required['sendmail_path'] = 'Sendmail Path';
            break;
    }

    $v = Validator::Get();

    foreach( $required as $field => $label )
    {
        $v->Register(Request::Get($field), Validator_Type::NOT_EMPTY, 'The ' . $label . ' field is required');
    }


    if( !$v->Validate() )
    {
        $output['message'] = 'Settings could not be saved; please fix the following items';
        $output['errors'] = $v->GetErrors();
        return JSON::Failure($output);
    }

    unset($_REQUEST['r']);

    // Setup mcf file for VP6 encoding
    if( $_REQUEST['video_format'] == Video_Converter::FORMAT_VP6 && preg_match('~^[0-9]+$~', $_REQUEST['video_bitrate']) )
    {
        $fp = fopen(INCLUDES_DIR . '/vp6.mcf', 'r+');
        fseek($fp, 0x14);
        fwrite($fp, pack('s', $_REQUEST['video_bitrate']));
        fclose($fp);
    }

    $_REQUEST['max_upload_size'] = Format::BytesToString(min(Format::StringToBytes($si->php_settings[ServerInfo::PHP_UPLOAD_MAX_FILESIZE]),
                                                             Format::StringToBytes($_REQUEST['max_upload_size'])));
    $_REQUEST['document_root'] = Dir::StripTrailingSlash($_REQUEST['document_root']);
    $_REQUEST['base_url'] = Dir::StripTrailingSlash($_REQUEST['base_url']);
    $_REQUEST['base_uri'] = parse_url($_REQUEST['base_url'], PHP_URL_PATH);
    $_REQUEST['template_uri'] = $_REQUEST['base_uri'] . '/templates/' . $_REQUEST['template'];

    if( Config::Get('template') != $_REQUEST['template'] )
    {
        tbxTemplateCacheFlush(true);
        TemplateRecompileAll(BASE_DIR . '/templates/' . $_REQUEST['template']);
    }

    ServerInfo::Get(true);
    Config::Save($_REQUEST);

    JSON::Success('Global software settings have been saved');
}

function tbxEmailTemplateSave()
{


    Privileges::Check(Privileges::TEMPLATES);

    $filename = TEMPLATES_DIR . '/' . File::Sanitize(Request::Get('template'));
    $is_global = stristr(Request::Get('template'), 'global');
    $v = Validator::Get();

    $v->Register(is_writable($filename), Validator_Type::IS_TRUE, 'Template file has incorrect permissions; change to 666 then try again');
    $v->Register(Request::Get('template_message'), Validator_Type::NOT_EMPTY, 'The Message field is required');

    if( !$is_global )
    {
        $v->Register(Request::Get('template_subject'), Validator_Type::NOT_EMPTY, 'The Subject field is required');
    }


    if( !$v->Validate() )
    {
        $output = array();
        $output['message'] = 'Settings could not be saved; please fix the following items';
        $output['errors'] = $v->GetErrors();
        return JSON::Failure($output);
    }


    if( $is_global )
    {
        file_put_contents($filename, Request::Get('template_message'));
    }
    else
    {
        $template_code['subject'] = Request::Get('template_subject');
        $template_code['message'] = Request::Get('template_message');

        file_put_contents($filename, serialize($template_code));
    }

    JSON::Success('Template has been successfully saved');
}

function tbxEmailTemplateLoad()
{
    Privileges::Check(Privileges::TEMPLATES);

    $output = array();
    $template = TEMPLATES_DIR . '/' . File::Sanitize(Request::Get('template'));
    $is_global = stristr(Request::Get('template'), 'global');

    if( $is_global )
    {
        $output['t_subject'] = '';
        $output['t_message'] = String::FormatNewlines(file_get_contents($template));
    }
    else
    {
        $template_code = unserialize(file_get_contents($template));

        $output['t_subject'] = $template_code['subject'];
        $output['t_message'] = String::FormatNewlines($template_code['message']);
    }

    JSON::Success($output);
}

function tbxEmailTemplateSearchReplaceShow()
{
    Privileges::Check(Privileges::TEMPLATES);

    $output = array();

    ob_start();
    include("cp-email-template-search-replace.php");
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxEmailTemplateSearchReplace()
{


    Privileges::Check(Privileges::TEMPLATES);

    $output = array();
    $v = Validator::Create();

    $v->Register(count(Request::Get('templates')), Validator_Type::GREATER, 'You must select at least one template for this action', 0);
    $v->Register(Request::Get('search'), Validator_Type::NOT_EMPTY, 'The Search For field is required');

    foreach( Request::Get('templates') as $template )
    {
        $template = File::Sanitize($template);
        $filename = TEMPLATES_DIR . '/' . $template;
        $v->Register(is_writeable($filename), Validator_Type::NOT_FALSE, "The template file $template has incorrect permissions; change to 666 then try again");
    }

    if( !$v->Validate() )
    {
        $output['message'] = 'Search and replace could not be executed; please fix the following items';
        $output['errors'] = $v->GetErrors();
        JSON::Failure($output);
    }
    else
    {
        $search = String::FormatNewlines(Request::Get('search'), String::NEWLINE_UNIX);
        $replace = String::FormatNewlines(Request::Get('replace'), String::NEWLINE_UNIX);
        $total_replacements = 0;

        foreach( Request::Get('templates') as $template )
        {
            $filename = TEMPLATES_DIR . '/' . File::Sanitize($template);

            if( stristr($template, 'global') )
            {
                $template_code = file_get_contents($filename);
                $template_code = str_replace($search, $replace, $template_code, $replacements);

                // Changes have been made
                if( $replacements > 0 )
                {
                    file_put_contents($filename, $template_code);
                    $total_replacements += $replacements;
                }
            }
            else
            {
                $template_code = unserialize(file_get_contents($filename));
                list($template_code['subject'], $template_code['message']) = str_replace($search, $replace, array($template_code['subject'], $template_code['message']), $replacements);

                // Changes have been made
                if( $replacements > 0 )
                {
                    file_put_contents($filename, serialize($template_code));
                    $total_replacements += $replacements;
                }
            }
        }

        $output['message'] = 'Search and replace has been completed.  Replacements made: ' .
                             NumberFormatInteger($total_replacements);

        JSON::Success($output);
    }
}

function tbxSiteTemplateSave()
{


    Privileges::Check(Privileges::TEMPLATES);

    $template = TEMPLATES_DIR . '/' . File::Sanitize(Request::Get('template'));
    $compiled = TEMPLATE_COMPILE_DIR . '/' . File::Sanitize(Request::Get('template'));

    if( !is_writable($template) )
    {
        return JSON::Failure(array('message' => 'Template file has incorrect permissions; change to 666 then try again'));
    }

    if( ($code = Template_Compiler::Compile(Request::Get('template_code'))) === false )
    {
        JSON::Failure(array('message' => 'Template contains errors', 'errors' => Template_Compiler::GetErrors()));
    }
    else
    {
        file_put_contents($template, Request::Get('template_code'));
        file_put_contents($compiled, $code);
        @chmod($compiled, 0666);

        JSON::Success('Template has been successfully saved');
    }
}

function tbxSiteTemplateLoad()
{
    Privileges::Check(Privileges::TEMPLATES);

    $output = array();
    $template = TEMPLATES_DIR . '/' . File::Sanitize(Request::Get('template'));
    $output['code'] = String::FormatNewlines(htmlspecialchars(file_get_contents($template)), String::NEWLINE_WINDOWS);

    JSON::Success($output);
}

function tbxSiteTemplateSearchReplaceShow()
{
    Privileges::Check(Privileges::TEMPLATES);

    $output = array();

    ob_start();
    include("cp-site-template-search-replace.php");
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxSiteTemplateSearchReplace()
{


    Privileges::Check(Privileges::TEMPLATES);

    $output = array();
    $v = Validator::Create();

    $v->Register(count(Request::Get('templates')), Validator_Type::GREATER, 'You must select at least one template for this action', 0);
    $v->Register(Request::Get('search'), Validator_Type::NOT_EMPTY, 'The Search For field is required');

    foreach( Request::Get('templates') as $template )
    {
        $template = File::Sanitize($template);
        $filename = TEMPLATES_DIR . '/' . $template;
        $v->Register(is_writeable($filename), Validator_Type::NOT_FALSE, "The template file $template has incorrect permissions; change to 666 then try again");
    }

    if( !$v->Validate() )
    {
        $output['message'] = 'Search and replace could not be executed; please fix the following items';
        $output['errors'] = $v->GetErrors();
        JSON::Failure($output);
    }
    else
    {
        $search = String::FormatNewlines(Request::Get('search'), String::NEWLINE_UNIX);
        $replace = String::FormatNewlines(Request::Get('replace'), String::NEWLINE_UNIX);
        $total_replacements = 0;

        foreach( Request::Get('templates') as $template )
        {
            $template = File::Sanitize($template);
            $filename = TEMPLATES_DIR . "/$template";

            $template_code = file_get_contents($filename);
            $template_code = str_replace($search, $replace, $template_code, $replacements);

            // Changes have been made
            if( $replacements > 0 && ($code = Template_Compiler::Compile($template_code)) !== false )
            {
                file_put_contents($filename, $template_code);
                file_put_contents(TEMPLATE_COMPILE_DIR . "/$template", $code);
                @chmod($compiled, 0666);

                $total_replacements += $replacements;
            }
        }

        $output['message'] = 'Search and replace has been completed.  Replacements made: ' .
                             NumberFormatInteger($total_replacements);

        JSON::Success($output);
    }
}

function tbxTemplateRecompileAll()
{
    Privileges::Check(Privileges::TEMPLATES);

    if( ($result = TemplateRecompileAll()) !== true )
    {
        return JSON::Failure($result);
    }

    JSON::Success('All templates have been recompiled!');
}

function tbxTemplateCacheFlush($quiet = false)
{
    Privileges::Check(Privileges::TEMPLATES);

    $dirs = Dir::ReadDirectories(TEMPLATE_CACHE_DIR, '~^[^.]~');

    foreach( $dirs as $dir )
    {


        Dir::Remove(TEMPLATE_CACHE_DIR . '/' . $dir);
    }

    if( !$quiet )
    {
        JSON::Success('Template cache has been flushed!');
    }
}

function tbxDatabaseQuery()
{


    Privileges::Check(Privileges::DATABASE);

    $DB = GetDB();
    $query = Request::Get('query');
    $output = array();

    if( String::IsEmpty($query) )
    {
        return JSON::Failure(array('message' => 'The Query field must be filled in'));
    }

    // A data retrieval query
    if( preg_match('~^(select|show|describe)~i', $query) )
    {
        $result = $DB->Query($query);
        $rows = $DB->NumRows($result);
        $fields = mysql_num_fields($result);

        $output['message'] = 'Database query has been successfully executed';
        $output['html'] = "<table cellpadding=\"4\" cellspacing=\"0\" border=\"0\">" .
                          "<tr>" .
                          "<td colspan=\"$fields\">Rows returned: " . NumberFormatInteger($rows) . "</td>" .
                          "</tr>" .
                          "<tr class=\"db-fields\">";

        for( $i = 0; $i < $fields; $i++ )
        {
            $name = mysql_field_name($result, $i);
            $output['html'] .= "<td><b>$name</b></td>";
        }

        $output['html'] .= '</tr>';

        $counter = 0;
        while( $row = mysql_fetch_array($result) )
        {
            $output['html'] .= "<tr class=\"db-rows\">";
            for( $i = 0; $i < $fields; $i++ )
            {
                $output['html'] .= "<td>" . htmlspecialchars($row[$i]) . "</td>";
            }
            $output['html'] .= "</tr>";

            if( ++$counter >= 100 )
            {
                break;
            }
        }
        $output['html'] .= '</table>';

        $DB->Free($result);
    }

    // An update query
    else
    {
        $affected = $DB->Update($query);
        $output['message'] = sprintf('Database query has been successfully executed.  A total of %s row%s affected',
                                     NumberFormatInteger($affected),
                                     ($affected == 1 ? ' was' : 's were'));
    }

    JSON::Success($output);
}

function tbxDatabaseBackup()
{


    Privileges::Check(Privileges::DATABASE);

    $filename = BASE_DIR . '/data/' . File::Sanitize(Request::Get('filename'));

    if( file_exists($filename) )
    {
        if( is_dir($filename) )
        {
            return JSON::Failure('The filename you have entered is a directory.  Please select a different filename.');
        }
        else if( !is_writeable($filename) )
        {
            return JSON::Failure('The file you have entered already exists but is not writeable.  Change the permissions on this file to 666 or select a different filename.');
        }
    }

    $si = ServerInfo::GetCached();

    if( !$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_PHP] )
    {
        Shell::ExecScript('cron.php --backup --file=' . escapeshellarg($filename));
        JSON::Success('Database backup has been started.  Allow a few minutes to complete, then continue with the next step of the backup process.');
    }
    else
    {
        $DB = GetDB();
        $tables = GetDBTables();

        $DB->DumpTables($tables, $filename);

        JSON::Success('Database backup has been completed');
    }
}

function tbxDatabaseRestore()
{


    Privileges::Check(Privileges::DATABASE);

    $filename = BASE_DIR . '/data/' . File::Sanitize(Request::Get('filename'));

    if( !is_file($filename) )
    {
        return JSON::Failure('The file you have selected no longer exists on the server.');
    }

    $si = ServerInfo::GetCached();

    if( !$si->shell_exec_disabled && $si->binaries[ServerInfo::BIN_PHP] )
    {
        Shell::ExecScript('cron.php --restore --file=' . escapeshellarg($filename));
        JSON::Success('Database restore has been started.  Allow a few minutes to complete, then continue with the next step of the restore process.');
    }
    else
    {
        $DB = GetDB();
        $DB->RestoreTables($filename);

        JSON::Success('Database restore has been completed');
    }
}

function tbxAutocomplete()
{
    $DB = GetDB();

    list($table, $field) = explode('.', Request::Get('field'));

    $result = $DB->Query('SELECT DISTINCT # FROM # WHERE # LIKE ? ORDER BY #', array($field, $table, $field, Request::Get('q') . '%', $field));

    while( $row = $DB->NextRow($result) )
    {
        echo $row[$field] . String::NEWLINE_UNIX;
    }

    $DB->Free($result);
}

function tbxGenericAction($type, $action)
{


    Privileges::Check(Privileges::FromType($type));

    $output = array('ids' => array());

    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="' . $type . '"]');
    $primary_key = $xtable->columns->primaryKey->val();
    $callback = 'tbx' . $xtable->naming->function . ucfirst($action);

    $matching = new MatchingItems($xtable);
    $result = $matching->handle;
    $amount = 0;

    while( $item = $DB->NextRow($result) )
    {
        if( call_user_func($callback, $item) )
        {
            $output['ids'][] = $item[$primary_key];
            $amount++;
        }
    }

    $DB->Free($result);

    $matching->SetCalculatedAmount($amount);

    if( is_file('js/cp-global-' . $action . '.js') )
    {
        $output['eval'] = file_get_contents('js/cp-global-' . $action . '.js');
    }

    $output['datetime'] = date(DATETIME_FRIENDLY);
    $output['date'] = date(DATE_FRIENDLY);
    $output['message'] = $amount > 0 ? 'Successfully ' . _T('CP:'.$action.'d') . ' the ' . $matching->message : 'No ' . $xtable->naming->textUpperPlural . ' were ' . _T('CP:'.$action.'d');
    JSON::Success($output);
}

function tbxGenericAdd($type)
{


    Privileges::Check(Privileges::FromType($type));

    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $table = $xtable->name->val();
    $function_name = 'tbx'.$xtable->naming->function->val().'Add';
    $function_exists = function_exists($function_name);
    $primary_key = $xtable->columns->primaryKey->val();

    $v = Validator::Create();
    $v->RegisterFromXml($xtable);

    if( !empty($xtable->custom) )
    {
        $xtable_custom = $schema->el('//table[name="'.$xtable->custom->val().'"]');
        $v->RegisterFromXml($xtable_custom);
    }

    if( $function_exists )
    {
        call_user_func($function_name, Phase::PRE_VALIDATE);
    }

    if( !$v->Validate() )
    {
        if( $function_exists )
        {
            call_user_func($function_name, Phase::VALIDATION_FAILED);
        }

        $output = array('message' => $xtable->naming->textUpper . ' could not be added; please fix the following items', 'errors' => $v->GetErrors());
        JSON::Failure($output);
    }
    else
    {
        if( $function_exists )
        {
            call_user_func($function_name, Phase::PRE_INSERT);
        }

        $_REQUEST[$primary_key] = DatabaseAdd($table, $_REQUEST);

        if( !empty($xtable->custom) )
        {
            DatabaseAdd($xtable->custom->val(), $_REQUEST);
        }

        if( $function_exists )
        {
            call_user_func($function_name, Phase::POST_INSERT);
        }

        JSON::Success($xtable->naming->textUpper . ' has been successfully added');
    }
}

function tbxGenericEdit($type)
{


    Privileges::Check(Privileges::FromType($type));

    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $table = $xtable->name->val();
    $function_name = 'tbx'.$xtable->naming->function.'Edit';
    $function_exists = function_exists($function_name);
    $primary_key = $xtable->columns->primaryKey->val();

    $v = Validator::Create();
    $v->RegisterFromXml($xtable);

    if( !empty($xtable->custom) )
    {
        $xtable_custom = $schema->el('//table[name="'.$xtable->custom->val().'"]');
        $v->RegisterFromXml($xtable_custom);
    }

    if( $function_exists )
    {
        call_user_func($function_name, Phase::PRE_VALIDATE);
    }

    if( !$v->Validate() )
    {
        if( $function_exists )
        {
            call_user_func($function_name, Phase::VALIDATION_FAILED);
        }

        $output = array('message' => $xtable->naming->textUpper . ' could not be updated; please fix the following items', 'errors' => $v->GetErrors());
        JSON::Failure($output);
    }
    else
    {
        if( $function_exists )
        {
            call_user_func($function_name, Phase::PRE_UPDATE);
        }

        $original = DatabaseUpdate($table, $_REQUEST);

        if( !empty($xtable->custom) )
        {
            DatabaseUpdate($xtable->custom->val(), $_REQUEST);
        }

        if( $function_exists )
        {
            call_user_func($function_name, Phase::POST_UPDATE);
        }

        $output = array('id' => $original[$primary_key],
                        'message' => $xtable->naming->textUpper . ' has been successfully updated',
                        'html' => SearchItemHtml($type, $original));

        JSON::Success($output);
    }
}

function tbxGenericShowEmail($type)
{
    Privileges::Check(Privileges::FromType($type));

    $output = array();

    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $xnaming = $xtable->naming;
    $matching = new MatchingItems($xtable);

    ob_start();
    include('cp-global-email.php');
    IncludeJavascript('js/cp-global-email.js');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxGenericEmail($type)
{


    Privileges::Check(Privileges::FromType($type));

    $output = array();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $xnaming = $xtable->naming;
    $v = Validator::Create();

    $v->Register(Request::Get('subject'), Validator_Type::NOT_EMPTY, 'The Subject field must be filled in');
    $v->Register(Request::Get('message'), Validator_Type::NOT_EMPTY, 'The Message field must be filled in');

    if( !$v->Validate() )
    {
        $output['message'] = 'E-mail message could not be sent; please fix the following items';
        $output['errors'] = $v->GetErrors();
        JSON::Failure($output);
    }
    else
    {
        $matching = new MatchingItems($xtable);
        $matching->ApplyFunction('tbx' . $xtable->naming->function->val() . 'Email');
        $output['message'] = 'E-mail message has been sent to the ' . $matching->message;
        JSON::Success($output);
    }
}

function tbxGenericShowBulkEdit($type)
{
    Privileges::Check(Privileges::FromType($type));

    $output = array();

    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $xnaming = $xtable->naming;
    $fields = array();

    // Prepare search and sort fields
    foreach( $xtable->xpath('./columns/column') as $xcolumn )
    {
        $col = $xtable->name . '.' . $xcolumn->name;
        $label = $xcolumn->label;

        if( !empty($xcolumn->admin->bulkEdit) )
        {
            $fields[] = array('column' => $col,
                              'label' => $label,
                              'attr' => 'actions="' . $xcolumn->admin->bulkEdit->val() . '"' .
                                        (empty($xcolumn->autocomplete) ? '' : ' acomplete="'.$xcolumn->autocomplete->val().'"'));
        }
    }

    $matching = new MatchingItems($xtable);

    ob_start();
    include('cp-global-bulk-edit.php');
    IncludeJavascript('js/cp-global-bulk-edit.js');
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxGenericBulkEdit($type)
{


    Privileges::Check(Privileges::FromType($type));

    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $xnaming = $xtable->naming;
    $function_name = 'tbx'.$xtable->naming->function.'BulkEdit';
    $function_exists = function_exists($function_name);
    $table = $xtable->name->val();
    $output = array();

    $v = Validator::Create();

    for( $i = 0; $i < count($_REQUEST['action']); $i++ )
    {
        $action = $_REQUEST['action'][$i];
        $value = $_REQUEST['value'][$i];
        switch($action)
        {
            case BulkEdit::ACTION_SET:
            case BulkEdit::ACTION_APPEND:
            case BulkEdit::ACTION_PREPEND:
            case BulkEdit::ACTION_RAW_SQL:
                $v->Register($value,
                             Validator_Type::NOT_EMPTY,
                             'The ' . $action . ' action requires that the Value field be filled in');
                break;

            case BulkEdit::ACTION_TRUNCATE:
            case BulkEdit::ACTION_ADD:
            case BulkEdit::ACTION_SUBTRACT:
                $v->Register($value,
                             Validator_Type::REGEX_MATCH,
                             'The ' . $action . ' action requires that the Value field be a numeric value',
                             '~^\d+~');
                break;

            case BulkEdit::ACTION_REPLACE:
                $v->Register($value,
                             Validator_Type::REGEX_MATCH,
                             'The ' . $action . ' action requires that the Value field contains a search string and replacement string separated by a comma',
                             '~^.+?,.*?$~');
                break;
        }
    }

    if( !$v->Validate() )
    {
        $output['message'] = 'Bulk changes could not be made; please fix the following items';
        $output['errors'] = $v->GetErrors();
        return JSON::Failure($output);
    }

    $v->Reset();

    $ub = new SQL_UpdateBuilder($table);
    for( $i = 0; $i < count($_REQUEST['action']); $i++ )
    {
        $whole_field = $_REQUEST['field'][$i];
        $value = $_REQUEST['value'][$i];
        $action = $_REQUEST['action'][$i];
        list($table, $field) = explode('.', $whole_field);

        if( $function_exists )
        {
            $value = call_user_func($function_name, Phase::PRE_UPDATE, $field, $value, $action);
        }

        switch($action)
        {
            case BulkEdit::ACTION_SET:
                $ub->AddSet($whole_field, '?', array($value));
                break;

            case BulkEdit::ACTION_APPEND:
                $ub->AddSet($whole_field, 'CONCAT(#.#,?)', array($table, $field, $value));
                break;

            case BulkEdit::ACTION_PREPEND:
                $ub->AddSet($whole_field, 'CONCAT(?,#.#)', array($value, $table, $field));
                break;

            case BulkEdit::ACTION_INCREMENT:
                $value = 1;
                // Fall through
            case BulkEdit::ACTION_ADD:
                $ub->AddSet($whole_field, '#.#+?', array($table, $field, $value));
                break;

            case BulkEdit::ACTION_DECREMENT:
                $value = 1;
                // Fall through
            case BulkEdit::ACTION_SUBTRACT:
                $ub->AddSet($whole_field, '#.#-?', array($table, $field, $value));
                break;

            case BulkEdit::ACTION_REPLACE:
                list($from, $to) = explode(',', $value);
                $ub->AddSet($whole_field, 'REPLACE(#.#,?,?)', array($table, $field, $from, $to));
                break;

            case BulkEdit::ACTION_TRUNCATE:
                $ub->AddSet($whole_field, 'SUBSTR(#.#,1,?)', array($table, $field, $value));
                break;

            case BulkEdit::ACTION_RAW_SQL:
                $ub->AddSet($whole_field, $value);
                break;

            case BulkEdit::ACTION_TRIM:
                $ub->AddSet($whole_field, 'TRIM(#.#)', array($table, $field));
                break;

            case BulkEdit::ACTION_CLEAR:
                $clear_value = null;
                if( stristr($xtable->el('.//column[name="'.$field.'"]/definition')->val(), 'NOT NULL') )
                {
                    $clear_value = '';
                }

                $ub->AddSet($whole_field, '?', array($clear_value));
                break;

            case BulkEdit::ACTION_UPPERCASE_ALL:
                $ub->AddSet($whole_field, 'UPPER(#.#)', array($table, $field));
                break;

            case BulkEdit::ACTION_UPPERCASE_FIRST:
                $ub->AddSet($whole_field, 'CONCAT(UPPER(SUBSTR(#.#, 1, 1)), LOWER(SUBSTR(#.#, 2)))', array($table, $field, $table, $field));
                break;

            case BulkEdit::ACTION_LOWERCASE_ALL:
                $ub->AddSet($whole_field, 'LOWER(#.#)', array($table, $field));
                break;
        }
    }

    if( !$v->Validate() )
    {
        $output['message'] = 'Bulk changes could not be made; please fix the following items';
        $output['errors'] = $v->GetErrors();
        return JSON::Failure($output);
    }

    $matching = new MatchingItems($xtable);
    $matching->ApplyDBUpdate($ub);

    if( $function_exists )
    {
        call_user_func($function_name, Phase::POST_UPDATE, $field, $value, $action);
    }

    $output['message'] = 'Changes have been applied to the ' . $matching->message;
    JSON::Success($output);
}

function tbxSavedSearchLoad()
{
    $DB = GetDB();
    $output = array();
    $search = $DB->Row('SELECT * FROM `tbx_saved_search` WHERE `search_id`=?', array(Request::Get('id')));

    if( $search )
    {
        $output['form'] = $search['form'];
    }

    JSON::Success($output);
}

function tbxSavedSearchAdd()
{


    $DB = GetDB();
    $output = array('message' => 'New saved search has been successfully created');

    $v = Validator::Create();

    $existing = $DB->QuerySingleColumn('SELECT COUNT(*) FROM `tbx_saved_search` WHERE `item_type`=? AND `identifier`=?', array(Request::Get('type'), Request::Get('identifier')));

    $v->Register(Request::Get('identifier'), Validator_Type::NOT_EMPTY, 'The identifier field must be filled in');
    $v->Register($existing, Validator_Type::LESS, 'The identifier you are trying to add already exists', 1);

    if( !$v->Validate() )
    {
        $output['message'] = 'Saved Search could not be added; please fix the following items';
        $output['errors'] = $v->GetErrors();
        JSON::Failure($output);
    }
    else
    {
        parse_str(Request::Get('form'), $form);
        $form = json_encode($form);

        $DB->Update('INSERT INTO `tbx_saved_search` VALUES (?,?,?,?)',
                             array(null,
                                   Request::Get('identifier'),
                                   Request::Get('type'),
                                   $form));

        $output['value'] = $DB->LastInsertId();
        $output['text'] = Request::Get('identifier');

        JSON::Success($output);
    }
}

function tbxSavedSearchEdit()
{


    $DB = GetDB();

    if( !empty($_REQUEST['id']) )
    {
        parse_str($_REQUEST['form'], $_REQUEST['form']);
        $_REQUEST['form'] = json_encode($_REQUEST['form']);

        $DB->Update('UPDATE `tbx_saved_search` SET ' .
                             '`form`=? ' .
                             'WHERE `search_id`=?',
                             array(Request::Get('form'),
                                   Request::Get('id')));
    }

    JSON::Success('The selected saved search has been successfully updated');
}

function tbxSavedSearchDelete()
{


    $DB = GetDB();
    $DB->Update('DELETE FROM `tbx_saved_search` WHERE `search_id`=?', array(Request::Get('id')));
    JSON::Success('The selected saved search has been successfully deleted');
}

function tbxGenericShowAdd($type)
{
    Privileges::Check(Privileges::FromType($type));

    $output = array();

    ob_start();
    include("cp-$type-add-edit.php");
    IncludeJavascript('js/cp-global-add.js');
    //IncludeJavascript("js/cp-$type-add.js");
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxGenericShowEdit($type)
{
    Privileges::Check(Privileges::FromType($type));

    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $xnaming = $xtable->naming;
    $table = $xtable->name->val();
    $primary_key = $xtable->columns->primaryKey->val();
    $editing = true;
    $output = array();

    $_REQUEST = $DB->Row('SELECT * FROM # WHERE #=?', array($table, $primary_key, Request::Get('id')));

    // Get user defined fields
    if( !empty($xtable->custom) )
    {
        $custom_data = $DB->Row('SELECT * FROM # WHERE #=?', array($xtable->custom->val(), $primary_key, Request::Get($primary_key)));

        if( is_array($custom_data) )
        {
            $_REQUEST = array_merge($custom_data, $_REQUEST);
        }
    }

    $original = $_REQUEST;
    $_REQUEST = String::HtmlSpecialChars($_REQUEST);

    ob_start();
    include("cp-$type-add-edit.php");
    IncludeJavascript('js/cp-global-edit.js');
    IncludeJavascript("js/cp-$type-edit.js");
    $output['html'] = ob_get_clean();

    JSON::Success($output);
}

function tbxFunctionMissing()
{
    if( Request::PostMaxSizeExceeded() )
    {
        JSON::Failure('The size of the data submitted surpasses the amount that PHP allows (' . ini_get('post_max_size') . ')');
    }
    else
    {
        throw new BaseException('Function argument was missing from the request');
    }
}

function tbxGenericSearch()
{
    $DB = GetDB();
    $schema = GetDBSchema();

    $_REQUEST['per_page'] = isset($_REQUEST['per_page']) && $_REQUEST['per_page'] > 0 ? $_REQUEST['per_page'] : 20;
    $_REQUEST['page'] = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? $_REQUEST['page'] : 1;

    // Sanity checking
    $table = Request::GetSafe('table');
    $xtable = $schema->el('//table[name="'.$table.'"]');
    if( empty($xtable) )
    {
        throw new BaseException('The supplied database table does not exist', $table);
    }

    // Get custom and merge tables
    $custom_table = $xtable->custom->val();
    $merge_tables = empty($custom_table) ? array() : array($custom_table);
    foreach( $xtable->xpath('./merge') as $xmerge )
    {
        $merge_tables[] = $xmerge->val();
    }

    // Start building the SQL query
    $s = new SQL_SelectBuilder($table);

    // Fulltext searches
    if( isset($_REQUEST['text_search']) && !String::IsEmpty($_REQUEST['text_search']) )
    {
        $columns = array();
        foreach( $xtable->xpath('.//fulltext[1]/column') as $xcolumn )
        {
            $columns[] = $table . '.' . $xcolumn->val();
        }

        $s->AddFulltextWhere($columns, $_REQUEST['text_search_type'], $_REQUEST['text_search']);

        if( $_REQUEST['text_search_type'] == SQL::FULLTEXT )
        {
            $_REQUEST['sort_field'] = array();
        }
    }

    // Standard search fields
    for( $i = 0; $i < count($_REQUEST['search_field']); $i++ )
    {
        $s->AddWhere($_REQUEST['search_field'][$i], $_REQUEST['search_operator'][$i], $_REQUEST['search_term'][$i], $_REQUEST['search_connector'][$i], true);
    }

    // Sort fields
    for( $i = 0; $i < count($_REQUEST['sort_field']); $i++ )
    {
        $s->AddOrder($_REQUEST['sort_field'][$i], $_REQUEST['sort_direction'][$i]);
    }

    $primary_key = $xtable->columns->primaryKey->val();
    $result = $DB->QueryWithPagination($s->Generate(), $s->Binds(), $_REQUEST['page'], $_REQUEST['per_page'], $primary_key);

    if( $result['handle'] )
    {
        $global_item_include_file = File::Sanitize('cp-' . $xtable->naming->type . '-search-item-global.php', 'php');
        $item_include_file = File::Sanitize('cp-' . $xtable->naming->type . '-search-item.php', 'php');

        if( !is_file("includes/$item_include_file") )
        {
            throw new BaseException('The required include file could not be found', $item_include_file);
        }

        ob_start();

        if( is_file("includes/$global_item_include_file") )
        {
            include($global_item_include_file);
        }

        while( $original = $DB->NextRow($result['handle']) )
        {
            foreach( $merge_tables as $merge_table )
            {
                $row = $DB->Row('SELECT * FROM # WHERE #=?', array($merge_table, $primary_key, $original[$primary_key]));

                if( is_array($row) )
                {
                    $original = array_merge($row, $original);
                }
            }

            $item = String::HtmlSpecialChars($original);
            include($item_include_file);
        }

        $result['html'] = ob_get_clean();

        $DB->Free($result['handle']);

        unset($result['handle']);
    }

    JSON::Success($result);
}

function SearchItemHtml($type, $original)
{
    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[naming/type="'.$type.'"]');
    $primary_key = $xtable->columns->primaryKey->val();
    $global_item_include_file = File::Sanitize('cp-' . $type . '-search-item-global.php', 'php');
    $item_include_file = File::Sanitize('cp-' . $type . '-search-item.php', 'php');

    // Get custom and merge tables
    $custom_table = $xtable->custom->val();
    $merge_tables = empty($custom_table) ? array() : array($custom_table);
    foreach( $xtable->xpath('./merge') as $xmerge )
    {
        $merge_tables[] = $xmerge->val();
    }

    foreach( $merge_tables as $merge_table )
    {
        $row = $DB->Row('SELECT * FROM # WHERE #=?', array($merge_table, $primary_key, $original[$primary_key]));

        if( is_array($row) )
        {
            $original = array_merge($row, $original);
        }
    }

    ob_start();

    if( is_file("includes/$global_item_include_file") )
    {
        include($global_item_include_file);
    }

    $item = String::HtmlSpecialChars($original);
    include($item_include_file);
    return ob_get_clean();
}


?>