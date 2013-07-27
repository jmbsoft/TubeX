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



$t = new Template();
$t->Assign('g_config', Config::GetAll());
$t->Assign('g_loc_upload', true);
$t->Assign('g_logged_in', isset($_COOKIE[LOGIN_COOKIE]));

if( !Config::Get('flag_allow_uploads') )
{
    $t->Display('upload-disabled.tpl');
    return;
}


// For flash uploads, cookie sent through post vars
if( isset($_GET['flash']) )
{
    if( isset($_POST['cookie']) )
    {
        $_COOKIE[LOGIN_COOKIE] = html_entity_decode($_POST['cookie']);
    }
    else
    {
        $t->Assign('g_errors', array(_T('Validation:PHP post_max_size exceeded')));
        $t->Display('upload-flash-errors.tpl');
        exit;
    }
}


if( !AuthenticateUser::Login() )
{
    header('Location: ' . Config::Get('base_url') . '/user.php?r=login&referrer=' . urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), true, 301);
    return;
}


if( isset($_COOKIE[LOGIN_COOKIE]) )
{
    parse_str($_COOKIE[LOGIN_COOKIE], $cookie);
    $t->Assign('g_username', $cookie['username']);
}

$functions = array('upload' => 'tbxDisplayUpload',
                   'upload-step-one' => 'tbxUploadStepOne',
                   'upload-step-two' => 'tbxUploadStepTwo');

if( isset($functions[$_REQUEST['r']]) )
{
    call_user_func($functions[$_REQUEST['r']]);
}
else
{
    tbxDisplayUpload();
}

function tbxDisplayUpload()
{
    global $t;

    $DB = GetDB();

    $t->Assign('g_tag_min_length', Tags::GetMinLength());
    $t->Assign('g_custom_fields', $DB->FetchAll('SELECT * FROM `tbx_video_custom_schema` WHERE `on_submit`=1'));
    $t->Assign('g_year', date('Y'));
    $t->Display('upload-step-one.tpl');
}

function tbxUploadStepOne()
{
    global $t;

    $v = Validator::Create();

    $_REQUEST['tags'] = Tags::Format($_REQUEST['tags']);

    $v->Register($_REQUEST['title'],
                 Validator_Type::LENGTH_BETWEEN,
                 _T('Validation:Invalid Length', _T('Label:Title'), Config::Get('title_min_length'), Config::Get('title_max_length')),
                 Config::Get('title_min_length') . ',' . Config::Get('title_max_length'));

    $v->Register($_REQUEST['description'],
                 Validator_Type::LENGTH_BETWEEN,
                 _T('Validation:Invalid Length', _T('Label:Description'), Config::Get('description_min_length'), Config::Get('description_max_length')),
                 Config::Get('description_min_length') . ',' . Config::Get('description_max_length'));

    $v->Register(Tags::Count($_REQUEST['tags']),
                 Validator_Type::IS_BETWEEN,
                 _T('Validation:Invalid Num Tags', Config::Get('tags_min'), Config::Get('tags_max')),
                 Config::Get('tags_min') . ',' . Config::Get('tags_max'));

    // Register user-defined field validators
    $schema = GetDBSchema();
    $v->RegisterFromXml($schema->el('//table[name="tbx_video_custom"]'), 'user', 'create');


    // Check blacklist
    $_REQUEST['ip_address'] = $_SERVER['REMOTE_ADDR'];
    if( ($match = Blacklist::Match($_REQUEST, Blacklist::ITEM_VIDEO)) !== false )
    {
        $v->SetError(_T('Validation:Blacklisted', $match['match']));
    }


    // Validate CAPTCHA
    if( Config::Get('flag_captcha_on_upload') )
    {
        Captcha::Verify();
    }


    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->AssignByRef('g_form', $_REQUEST);
        return tbxDisplayUpload();
    }

    $_REQUEST['step_one_data'] = base64_encode(serialize($_REQUEST));
    $_REQUEST['step_one_sig'] = sha1($_REQUEST['step_one_data'] . Config::Get('random_value'));

    $t->Assign('g_file_types', '*.' . str_replace(',', ';*.', Config::Get('upload_extensions')));
    $t->Assign('g_cookie', $_COOKIE[LOGIN_COOKIE]);
    $t->AssignByRef('g_form', $_REQUEST);
    $t->Display('upload-step-two.tpl');
}

function tbxUploadStepTwo()
{
    global $t;

    $upload = $_FILES['video_file'];
    $v = Validator::Create();
    $DB = GetDB();

    $v->Register(sha1($_REQUEST['step_one_data'] . Config::Get('random_value')) == $_REQUEST['step_one_sig'],
                 Validator_Type::IS_TRUE,
                 _T('Validation:Video Data Altered'));

    $v->Register($upload['error'] == UPLOAD_ERR_OK, Validator_Type::IS_TRUE, Uploads::CodeToMessage($upload['error']));

    if( is_uploaded_file($upload['tmp_name']) )
    {
        $max_filesize = Format::StringToBytes(Config::Get('max_upload_size'));
        $max_duration = Format::DurationToSeconds(Config::Get('max_upload_duration'));
        $extensions = str_replace(',', '|', Config::Get('upload_extensions'));

        $v->Register($upload['size'], Validator_Type::IS_BETWEEN, _T('Validation:Video size too large'), '1,' . $max_filesize);
        $v->Register(File::Extension($upload['name']), Validator_Type::REGEX_MATCH, _T('Validation:Video file extension not allowed'), '~^(' . $extensions . ')$~');

        try
        {
            $vi = new Video_Info($upload['tmp_name']);
            $vi->Extract();
            $v->Register($vi->length, Validator_Type::LESS_EQ, _T('Validation:Video duration too long'), $max_duration);
        }
        catch(Exception $e)
        {
            $v->Register(false, Validator_Type::IS_TRUE, $e->getMessage());
        }

        $md5 = md5_file($upload['tmp_name']);
        if( Config::Get('flag_upload_reject_duplicates') )
        {
            $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_video_md5sum` WHERE `md5`=?', array($md5)),
                         Validator_Type::IS_ZERO,
                         _T('Validation:Duplicate video'));
        }
    }

    // Validate input
    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->AssignByRef('g_form', $_REQUEST);

        if( isset($_REQUEST['flash']) )
        {
            $t->Display('upload-flash-errors.tpl');
        }
        else
        {
            $t->Assign('g_file_types', '*.' . str_replace(',', ';*.', Config::Get('upload_extensions')));
            $t->Assign('g_cookie', $_COOKIE[LOGIN_COOKIE]);
            $t->Display('upload-step-two.tpl');
        }

        return;
    }

    $_REQUEST = array_merge($_REQUEST, unserialize(base64_decode($_REQUEST['step_one_data'])));

    Form_Prepare::Standard('tbx_video');
    Form_Prepare::Standard('tbx_video_stat');
    Form_Prepare::Custom('tbx_video_custom_schema', 'on_submit');

    $_REQUEST['duration'] = $vi->length;
    $_REQUEST['date_added'] = Database_MySQL::Now();
    $_REQUEST['username'] = AuthenticateUser::GetUsername();
    $_REQUEST['is_private'] = Config::Get('flag_upload_allow_private') ? intval($_REQUEST['is_private']) : 0;
    $_REQUEST['allow_ratings'] = intval($_REQUEST['allow_ratings']);
    $_REQUEST['allow_embedding'] = intval($_REQUEST['allow_embedding']);
    $_REQUEST['allow_comments'] = intval($_REQUEST['allow_comments']) ? 'Yes - Add Immediately' : 'No';
    $_REQUEST['is_user_submitted'] = 1;

    if( $_REQUEST['recorded_day'] && $_REQUEST['recorded_month'] && $_REQUEST['recorded_year'] )
    {
        $_REQUEST['date_recorded'] = $_REQUEST['recorded_year'] . '-' . $_REQUEST['recorded_month'] . '-' . $_REQUEST['recorded_day'];
    }

    // Strip HTML tags
    if( Config::Get('flag_video_strip_tags') )
    {
        $_REQUEST = String::StripTags($_REQUEST);
    }

    // Configure status
    $_REQUEST['status'] = STATUS_ACTIVE;
    if( Config::Get('flag_upload_convert') )
    {
        $_REQUEST['status'] = STATUS_QUEUED;
        $_REQUEST['next_status'] = Config::Get('flag_upload_review') ? STATUS_PENDING : STATUS_ACTIVE;
    }
    else if( Config::Get('flag_upload_review') )
    {
        $_REQUEST['status'] = STATUS_PENDING;
    }


    // Add to database
    $_REQUEST['video_id'] = DatabaseAdd('tbx_video', $_REQUEST);
    DatabaseAdd('tbx_video_custom', $_REQUEST);
    DatabaseAdd('tbx_video_stat', $_REQUEST);


    if( $_REQUEST['status'] == STATUS_ACTIVE && !$_REQUEST['is_private'] )
    {
        Tags::AddToFrequency($_REQUEST['tags']);
    }

    // Mark for conversion
    else if( $_REQUEST['status'] == STATUS_QUEUED )
    {
        DatabaseAdd('tbx_conversion_queue', array('video_id' => $_REQUEST['video_id'], 'queued' => time()));
    }


    // Mark as private
    if( $_REQUEST['is_private'] )
    {
        $_REQUEST['private_id'] = sha1(uniqid(rand(), true));
        DatabaseAdd('tbx_video_private', $_REQUEST);
    }


    // Setup video files and generate thumbnails
    $directory = Video_Dir::DirNameFromId($_REQUEST['video_id']);
    $vd = new Video_Dir($directory);
    $clip = $vd->AddClipFromFile($upload['tmp_name'], File::Extension($upload['name']));

    if( Video_FrameGrabber::CanGrab() )
    {
        Video_FrameGrabber::Grab($clip,
                                 $vd->GetThumbsDir(),
                                 Config::Get('thumb_amount'),
                                 Config::Get('thumb_quality'),
                                 Config::Get('thumb_size'),
                                 $vi);
    }


    foreach( $vd->GetClipURIs() as $clip )
    {
        $_REQUEST['clip'] = $clip;
        $_REQUEST['filesize'] = filesize(Config::Get('document_root') . $clip);
        DatabaseAdd('tbx_video_clip', $_REQUEST);
    }

    $thumb_ids = array();
    foreach( $vd->GetThumbURIs() as $thumb )
    {
        $_REQUEST['thumbnail'] = $thumb;
        $thumb_ids[] = DatabaseAdd('tbx_video_thumbnail', $_REQUEST);
    }


    // Select the display thumbnail
    $num_thumbnails = count($thumb_ids);
    $display_thumbnail = null;
    if( $num_thumbnails > 0 )
    {
        $display_thumbnail = $thumb_ids[rand(0, floor(0.40 * $num_thumbnails))];
    }

    DatabaseUpdate('tbx_video', array('video_id' => $_REQUEST['video_id'], 'num_thumbnails' => $num_thumbnails, 'display_thumbnail' => $display_thumbnail));


    // Add MD5 sum for prevention of duplicates
    $DB->Update('REPLACE INTO `tbx_video_md5sum` VALUES (?)', array($md5));


    // Update user stats
    StatsRollover();
    $DB->Update('UPDATE `tbx_user_stat` SET ' .
                '`today_videos_uploaded`=`today_videos_uploaded`+1,' .
                '`week_videos_uploaded`=`week_videos_uploaded`+1,' .
                '`month_videos_uploaded`=`month_videos_uploaded`+1,' .
                '`total_videos_uploaded`=`total_videos_uploaded`+1 ' .
                'WHERE `username`=?',
                array($_REQUEST['username']));


    $t->AssignByRef('g_form', $_REQUEST);
    $t->AssignByRef('g_video', $_REQUEST);
    $t->Display(isset($_REQUEST['flash']) ? 'upload-flash-complete.tpl' : 'upload-complete.tpl');

    UpdateCategoryStats($_REQUEST['category_id']);

    if( !Config::Get('flag_using_cron') && $_REQUEST['status'] == STATUS_QUEUED )
    {
        ConversionQueue::Start();
    }
}

?>