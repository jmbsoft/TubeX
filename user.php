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


$t = new Template();
$t->Assign('g_config', Config::GetAll());
$t->Assign('g_logged_in', isset($_COOKIE[LOGIN_COOKIE]));

if( isset($_COOKIE[LOGIN_COOKIE]) )
{
    parse_str($_COOKIE[LOGIN_COOKIE], $cookie);
    $t->Assign('g_username', $cookie['username']);
}

$functions = array('logout' => array('r' => 'tbxLogout', 'auth' => false),
                   'reset' => array('r' => 'tbxDisplayReset', 'auth' => false),
                   'reset-submit' => array('r' => 'tbxReset', 'auth' => false),
                   'reset-confirm' => array('r' => 'tbxDisplayResetConfirm', 'auth' => false),
                   'favorites' => array('r' => 'tbxDisplayFavorites', 'auth' => true),
                   'favorite-remove' => array('r' => 'tbxFavoriteRemove', 'auth' => true),
                   'login' => array('r' => 'tbxDisplayLogin', 'auth' => false),
                   'login-submit' => array('r' => 'tbxLogin', 'auth' => false),
                   'video-edit' => array('r' => 'tbxDisplayVideoEdit', 'auth' => true),
                   'video-edit-submit' => array('r' => 'tbxVideoEdit', 'auth' => true),
                   'video-delete' => array('r' => 'tbxVideoDelete', 'auth' => true),
                   'edit' => array('r' => 'tbxDisplayEdit', 'auth' => true),
                   'edit-submit' => array('r' => 'tbxEdit', 'auth' => true),
                   'register' => array('r' => 'tbxDisplayRegister', 'auth' => false),
                   'register-submit' => array('r' => 'tbxRegister', 'auth' => false),
                   'register-confirm' => array('r' => 'tbxDisplayRegisterConfirm', 'auth' => false),
                   'my-videos' => array('r' => 'tbxDisplayMyVideos', 'auth' => true),
                   'avatar' => array('r' => 'tbxDisplayAvatarEdit', 'auth' => true),
                   'avatar-submit' => array('r' => 'tbxAvatarEdit', 'auth' => true),
                   'my-account' => array('r' => 'tbxDisplayMyAccount', 'auth' => false));

$r = $_REQUEST['r'];
if( isset($functions[$r]) )
{
    if( $functions[$r]['auth'] )
    {
        $_REQUEST['referrer'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        AuthenticateUser::Login('tbxDisplayLogin');
    }

    call_user_func($functions[$r]['r']);
}
else if( isset($_COOKIE[LOGIN_COOKIE]) )
{
    tbxDisplayMyAccount();
}
else
{
    tbxDisplayLogin();
}

function tbxDisplayMyVideos()
{
    global $t;

    $sorters = array('added' => '`date_added` DESC',
                     'title' => '`title`',
                     'duration' => '`duration` DESC',
                     'rating' => '`total_avg_rating` DESC',
                     'views' => '`total_num_views` DESC');

    $_REQUEST['p'] = empty($_REQUEST['p']) ? 1 : $_REQUEST['p'];
    $_REQUEST['s'] = isset($sorters[$_REQUEST['s']]) ? $_REQUEST['s'] : 'added';

    $t->Assign('g_my_videos_term', $_REQUEST['t']);
    $t->Assign('g_sorter', $_REQUEST['s']);
    $t->Assign('g_username', AuthenticateUser::GetUsername());
    $t->Assign('g_videos_sorter', $sorters[$_REQUEST['s']]);
    $t->Assign('g_page_number', $_REQUEST['p']);
    $t->Display('user-my-videos.tpl');
}

function tbxDisplayFavorites()
{
    global $t;

    $_REQUEST['p'] = empty($_REQUEST['p']) ? 1 : $_REQUEST['p'];

    $t->Assign('g_username', AuthenticateUser::GetUsername());
    $t->Assign('g_page_number', $_REQUEST['p']);
    $t->Display('user-favorites.tpl');
}

function tbxFavoriteRemove()
{


    $DB = GetDB();
    $username = AuthenticateUser::GetUsername();
    $DB->Update('DELETE FROM `tbx_user_favorite` WHERE `username`=? AND `video_id`=?', array($username, $_REQUEST['video_id']));
}

function tbxDisplayVideoEdit($fromdb = true)
{
    global $t;

    $DB = GetDB();
    $username = AuthenticateUser::GetUsername();
    $video = $fromdb ?
             $DB->Row('SELECT * FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) WHERE `username`=? AND `tbx_video`.`video_id`=?', array($username, $_REQUEST['video_id'])) :
             $_REQUEST;

    if( !empty($video) )
    {
        list($video['recorded_year'], $video['recorded_month'], $video['recorded_day']) = explode('-', $video['date_recorded']);
    }

    $t->Assign('g_custom_fields', $DB->FetchAll('SELECT * FROM `tbx_video_custom_schema` WHERE `on_edit`=1'));
    $t->Assign('g_thumbs', $DB->FetchAll('SELECT * FROM `tbx_video_thumbnail` WHERE `video_id`=?', array($video['video_id'])));
    $t->Assign('g_username', $username);
    $t->Assign('g_form', $video);
    $t->Assign('g_year', date('Y'));
    $t->Assign('g_tag_min_length', Tags::GetMinLength());
    $t->Display('user-video-edit.tpl');
}

function tbxVideoEdit()
{
    global $t;


    $DB = GetDB();
    $username = AuthenticateUser::GetUsername();
    $video = $DB->Row('SELECT * FROM `tbx_video` JOIN `tbx_video_custom` USING (`video_id`) WHERE `username`=? AND `tbx_video`.`video_id`=?', array($username, $_REQUEST['video_id']));
    $_REQUEST['tags'] = Tags::Format($_REQUEST['tags']);

    $v = Validator::Create();

    $v->Register(empty($video), Validator_Type::IS_FALSE, _T('Validation:Not your video'));

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

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        return tbxDisplayVideoEdit(false);
    }

    // Strip HTML tags
    if( Config::Get('flag_video_strip_tags') )
    {
        $_REQUEST = String::StripTags($_REQUEST);
    }

    // Prepare fields for database
    Form_Prepare::Standard('tbx_video', 'edit');
    Form_Prepare::Custom('tbx_video_custom_schema', 'on_edit');

    $_REQUEST['video_id'] = $video['video_id'];
    $_REQUEST['display_thumbnail'] = $DB->QuerySingleColumn('SELECT `thumbnail_id` FROM `tbx_video_thumbnail` WHERE `video_id`=? AND `thumbnail_id`=?', array($video['video_id'], $_REQUEST['display_thumbnail']));
    $_REQUEST['is_private'] = Config::Get('flag_upload_allow_private') ? intval($_REQUEST['is_private']) : 0;
    $_REQUEST['allow_ratings'] = intval($_REQUEST['allow_ratings']);
    $_REQUEST['allow_embedding'] = intval($_REQUEST['allow_embedding']);
    $_REQUEST['allow_comments'] = intval($_REQUEST['allow_comments']) ? 'Yes - Add Immediately' : 'No';

    if( $_REQUEST['recorded_day'] && $_REQUEST['recorded_month'] && $_REQUEST['recorded_year'] )
    {
        $_REQUEST['date_recorded'] = $_REQUEST['recorded_year'] . '-' . $_REQUEST['recorded_month'] . '-' . $_REQUEST['recorded_day'];
    }

    if( empty($_REQUEST['display_thumbnail']) )
    {
        unset($_REQUEST['display_thumbnail']);
    }

    DatabaseUpdate('tbx_video', $_REQUEST);
    DatabaseUpdate('tbx_video_custom', $_REQUEST);


    // Handle changes to privacy
    if( $_REQUEST['is_private'] && !$video['is_private'] )
    {
        $private_id = sha1(uniqid(mt_rand(), true));
        $DB->Update('REPLACE INTO `tbx_video_private` VALUES (?,?)', array($video['video_id'], $private_id));
    }
    else if( !$_REQUEST['is_private'] )
    {
        $DB->Update('DELETE FROM `tbx_video_private` WHERE `video_id`=?', array($video['video_id']));
    }


    $t->ClearCache('video-watch.tpl', $video['video_id']);

    $t->Assign('g_success', true);
    tbxDisplayVideoEdit();
}

function tbxVideoDelete()
{


    $DB = GetDB();
    $username = AuthenticateUser::GetUsername();
    $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `username`=? AND `video_id`=?', array($username, $_REQUEST['video_id']));

    if( !empty($video) )
    {
        DeleteVideo($video);
    }
}

function tbxDisplayEdit()
{
    global $t;

    $DB = GetDB();

    $t->Assign('g_custom_fields', $DB->FetchAll('SELECT * FROM `tbx_user_custom_schema` WHERE `on_edit`=1'));
    $t->Assign('g_username', AuthenticateUser::GetUsername());
    $t->Display('user-edit.tpl');
}

function tbxEdit()
{
    global $t;


    $username = AuthenticateUser::GetUsername();
    $v = Validator::Create();
    $DB = GetDB();

    if( !empty($_REQUEST['new_password']) )
    {
        $v->Register($_REQUEST['new_password'], Validator_Type::LENGTH_GREATER_EQ, _T('Validation:Length Greater Equal', _T('Label:New Password'), 8), 8);
        $v->Register($_REQUEST['new_password'], Validator_Type::EQUALS, _T('Validation:Passwords do not match'), $_REQUEST['confirm_password']);
        $_REQUEST['password'] = sha1($_REQUEST['new_password']);
    }

    $v->Register($_REQUEST['email'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:E-mail')));
    $v->Register($_REQUEST['email'], Validator_Type::VALID_EMAIL, _T('Validation:E-mail', _T('Label:E-mail')));
    $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `email`=? AND `username`!=?', array($_REQUEST['email'], $username)), Validator_Type::IS_ZERO, _T('Validation:E-mail Taken'));
    $v->Register($_REQUEST['name'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Name')));
    $v->Register($_REQUEST['gender'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Gender')));

    if( !empty($_REQUEST['website_url']) )
    {
        $v->Register($_REQUEST['website_url'], Validator_Type::VALID_HTTP_URL, _T('Validation:HTTP URL', _T('Label:Website URL')));
    }


    // Register user-defined field validators
    $schema = GetDBSchema();
    $v->RegisterFromXml($schema->el('//table[name="tbx_user_custom"]'), 'user', 'edit');


    // Check blacklist
    $_REQUEST['ip_address'] = $_SERVER['REMOTE_ADDR'];
    if( ($match = Blacklist::Match($_REQUEST, Blacklist::ITEM_USER)) !== false )
    {
        $v->SetError(_T('Validation:Blacklisted', $match['match']));
    }

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->Assign('g_form', $_REQUEST);
        return tbxDisplayEdit();
    }

    // Strip HTML tags
    if( Config::Get('flag_user_strip_tags') )
    {
        $_REQUEST = String::StripTags($_REQUEST);
    }

    // Prepare fields for database
    Form_Prepare::Standard('tbx_user', 'edit');
    Form_Prepare::Custom('tbx_user_custom_schema', 'on_edit');

    $_REQUEST['username'] = $username;

    DatabaseUpdate('tbx_user', $_REQUEST);
    DatabaseUpdate('tbx_user_custom', $_REQUEST);

    $t->ClearCache('user-profile.tpl', $username);

    $t->Assign('g_success', true);
    tbxDisplayEdit();
}

function tbxDisplayAvatarEdit()
{
    global $t;

    $t->Display('user-avatar.tpl');
}

function tbxAvatarEdit()
{
    global $t;


    $DB = GetDB();
    $v = Validator::Create();

    Uploads::ProcessNew(Config::Get('avatar_extensions'));
    $upload = Uploads::Get('avatar_file');

    $v->Register(empty($upload), Validator_Type::IS_FALSE, _T('Validation:No image uploaded'));

    if( !empty($upload) )
    {
        $v->Register(empty($upload['error']), Validator_Type::IS_TRUE, $upload['error']);

        $imagesize = @getimagesize($upload['path']);
        $v->Register($imagesize, Validator_Type::NOT_FALSE, _T('Validation:Invalid image upload'));

        // Check dimensions and filesize
        if( $imagesize !== false )
        {
            list($width, $height) = explode('x', Config::Get('avatar_dimensions'));
            $v->Register($imagesize[0] > $width || $imagesize[1] > $height, Validator_Type::IS_FALSE, _T('Validation:Invalid image dimensions', Config::Get('avatar_dimensions')));
            $v->Register(filesize($upload['path']), Validator_Type::LESS_EQ, _T('Validation:Invalid image size', Config::Get('avatar_filesize')), Format::StringToBytes(Config::Get('avatar_filesize')));
        }
    }

    if( $v->Validate() )
    {
        $user = $DB->Row('SELECT * FROM `tbx_user` WHERE `username`=?', array(AuthenticateUser::GetUsername()));

        if( !empty($user['avatar_id']) )
        {
            Uploads::RemoveExisting($user['avatar_id']);
        }

        DatabaseUpdate('tbx_user', array('username' => $user['username'], 'avatar_id' => $upload['upload_id']));

        $t->Assign('g_success', true);
    }
    else
    {
        Uploads::RemoveCurrent();
        $t->Assign('g_errors', $v->GetErrors());
    }

    $t->Display('user-avatar.tpl');
}

function tbxDisplayRegister()
{
    global $t;

    $DB = GetDB();

    $t->Assign('g_custom_fields', $DB->FetchAll('SELECT * FROM `tbx_user_custom_schema` WHERE `on_submit`=1'));
    $t->Assign('g_year', date('Y'));
    $t->Assign('g_loc_register', true);
    $t->Display('user-register.tpl');
}

function tbxRegister()
{
    global $t;

    $DB = GetDB();
    $v = Validator::Create();

    $v->Register($_REQUEST['username'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Username')));
    $v->Register($_REQUEST['username'], Validator_Type::IS_ALPHANUM, _T('Validation:Alphanumeric', _T('Label:Username')));
    $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `username`=?', array($_REQUEST['username'])), Validator_Type::IS_ZERO, _T('Validation:Username Taken'));
    $v->Register($_REQUEST['password'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Password')));
    $v->Register($_REQUEST['password'], Validator_Type::LENGTH_GREATER_EQ, _T('Validation:Length Greater Equal', _T('Label:Password'), 8), 8);
    $v->Register($_REQUEST['password'], Validator_Type::EQUALS, _T('Validation:Passwords do not match'), $_REQUEST['confirm_password']);
    $v->Register($_REQUEST['email'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:E-mail')));
    $v->Register($_REQUEST['email'], Validator_Type::VALID_EMAIL, _T('Validation:E-mail', _T('Label:E-mail')));
    $v->Register($DB->QueryCount('SELECT COUNT(*) FROM `tbx_user` WHERE `email`=?', array($_REQUEST['email'])), Validator_Type::IS_ZERO, _T('Validation:E-mail Taken'));
    $v->Register($_REQUEST['name'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Name')));
    $v->Register(empty($_REQUEST['birth_month']) || empty($_REQUEST['birth_day']) || empty($_REQUEST['birth_year']), Validator_Type::IS_FALSE, _T('Validation:Birthday Required'));
    $v->Register($_REQUEST['gender'], Validator_Type::NOT_EMPTY, _T('Validation:Required', _T('Label:Gender')));
    $v->Register($_REQUEST['terms'], Validator_Type::NOT_EMPTY, _T('Validation:Accept Terms'));


    // Register user-defined field validators
    $schema = GetDBSchema();
    $v->RegisterFromXml($schema->el('//table[name="tbx_user_custom"]'), 'user', 'create');


    // Check blacklist
    $_REQUEST['ip_address'] = $_SERVER['REMOTE_ADDR'];
    if( ($match = Blacklist::Match($_REQUEST, Blacklist::ITEM_USER)) !== false )
    {
        $v->SetError(_T('Validation:Blacklisted', $match['match']));
    }


    // Check CAPTCHA
    if( Config::Get('flag_captcha_on_signup') )
    {
        Captcha::Verify();
    }

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->Assign('g_form', $_REQUEST);
        return tbxDisplayRegister();
    }

    // Format data
    $_REQUEST['date_birth'] = $_REQUEST['birth_year'] . '-' . $_REQUEST['birth_month'] . '-' . $_REQUEST['birth_day'];
    $_REQUEST['date_created'] = Database_MySQL::Now();
    $_REQUEST['user_level_id'] = $DB->QuerySingleColumn('SELECT `user_level_id` FROM `tbx_user_level` WHERE `is_default`=1');
    $_REQUEST['password'] = sha1($_REQUEST['password']);

    // Strip HTML tags
    if( Config::Get('flag_user_strip_tags') )
    {
        $_REQUEST = String::StripTags($_REQUEST);
    }

    // Prepare fields for database
    Form_Prepare::Standard('tbx_user');
    Form_Prepare::Standard('tbx_user_stat');
    Form_Prepare::Custom('tbx_user_custom_schema', 'on_submit');


    // Setup account status
    $_REQUEST['status'] = STATUS_ACTIVE;
    $email_template = 'email-user-added.tpl';
    if( Config::Get('flag_user_confirm_email') )
    {
        $_REQUEST['status'] = STATUS_SUBMITTED;
        $email_template = 'email-user-confirm.tpl';
    }
    else if( Config::Get('flag_user_approve') )
    {
        $_REQUEST['status'] = STATUS_PENDING;
        $email_template = 'email-user-pending.tpl';
    }


    // Add data to the database
    DatabaseAdd('tbx_user', $_REQUEST);
    DatabaseAdd('tbx_user_custom', $_REQUEST);
    DatabaseAdd('tbx_user_stat', $_REQUEST);

    if( $_REQUEST['status'] == STATUS_SUBMITTED )
    {
        $_REQUEST['register_code'] = sha1(uniqid(mt_rand(), true));
        $_REQUEST['timestamp'] = time();

        DatabaseAdd('tbx_user_register_code', $_REQUEST);

        $t->Assign('g_code', $_REQUEST['register_code']);
    }

    $t->AssignByRef('g_user', $_REQUEST);
    $t->AssignByRef('g_form', $_REQUEST);

    // Send e-mail message
    $m = new Mailer();
    $m->Mail($email_template, $t, $_REQUEST['email'], $_REQUEST['name']);

    // Display confirmation
    $t->Display('user-register-complete.tpl');
}

function tbxDisplayRegisterConfirm()
{
    global $t;

    $DB = GetDB();
    $v = Validator::Create();

    // Remove expired codes
    $DB->Update('DELETE FROM `tbx_user_register_code` WHERE `timestamp` < ?', array(time() - 86400));

    $confirmation = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_register_code` USING (`username`) WHERE `register_code`=?', array($_REQUEST['code']));

    $v->Register(empty($confirmation), Validator_Type::IS_FALSE, _T('Validation:Invalid confirmation code'));

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
    }
    else
    {
        $DB->Update('DELETE FROM `tbx_user_register_code` WHERE `username`=?', array($confirmation['username']));
        $user = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_custom` USING (`username`) JOIN `tbx_user_stat` USING (`username`) WHERE `tbx_user`.`username`=?', array($confirmation['username']));

        // Setup account status
        $user['status'] = STATUS_ACTIVE;
        $email_template = 'email-user-added.tpl';
        if( Config::Get('flag_user_approve') )
        {
            $user['status'] = STATUS_PENDING;
            $email_template = 'email-user-pending.tpl';
        }

        DatabaseUpdate('tbx_user', array('username' => $user['username'], 'status' => $user['status']));

        // Display confirmation
        $t->AssignByRef('g_user', $user);
        $t->AssignByRef('g_form', $user);

        // Send e-mail message
        $m = new Mailer();
        $m->Mail($email_template, $t, $user['email'], $user['name']);
    }

    $t->Display('user-confirmed.tpl');
}

function tbxDisplayMyAccount($authenticated = false)
{
    global $t;

    if( !$authenticated )
    {
        $_REQUEST['referrer'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        AuthenticateUser::Login('tbxDisplayLogin');
    }

    $DB = GetDB();

    $t->Assign('g_num_favorites', $DB->QueryCount('SELECT COUNT(*) FROM `tbx_user_favorite` WHERE `username`=?', array(AuthenticateUser::GetUsername())));
    $t->Assign('g_loc_my_account', true);
    $t->Assign('g_username', AuthenticateUser::GetUsername());
    $t->Display('user-my-account.tpl');
}

function tbxDisplayLogin()
{
    global $t;

    if( !isset($_REQUEST['referrer']) )
    {
        $_REQUEST['referrer'] = $_SERVER['HTTP_REFERER'];
    }

    $t->Assign('g_referrer', $_REQUEST['referrer']);
    $t->Assign('g_loc_login', true);
    $t->Display('user-login.tpl');
}

function tbxLogin()
{
    global $t;

    AuthenticateUser::Login();

    $v = Validator::Create();
    $v->Register(AuthenticateUser::Authenticated(), Validator_Type::IS_TRUE, AuthenticateUser::GetError());

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->Assign('g_form', $_REQUEST);
        return tbxDisplayLogin();
    }

    if( empty($_REQUEST['referrer']) || stristr($_REQUEST['referrer'], Config::Get('base_url')) === false )
    {
        $t->Assign('g_logged_in', true);
        tbxDisplayMyAccount(true);
    }
    else
    {
        header('Location: ' . $_REQUEST['referrer']);
    }
}

function tbxLogout()
{
    AuthenticateUser::Logout();
    header('Location: ' . Config::Get('base_url'));
    exit;
}

function tbxDisplayReset()
{
    global $t;

    $t->Display('user-reset.tpl');
}

function tbxReset()
{
    global $t;

    $DB = GetDB();
    $v = Validator::Create();

    $user = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_custom` USING (`username`) JOIN `tbx_user_stat` USING (`username`) WHERE `email`=?', array($_REQUEST['email']));

    $v->Register(empty($user), Validator_Type::IS_FALSE, _T('Validation:E-mail not found'));

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
        $t->Assign('g_form', $_REQUEST);
        return tbxDisplayReset();
    }

    $user['reset_code'] = sha1(uniqid(mt_rand(), true));
    $user['timestamp'] = time();

    $DB->Update('DELETE FROM `tbx_user_reset_code` WHERE `username`=? OR `timestamp` < ?', array($user['username'], time() - 3600));
    DatabaseAdd('tbx_user_reset_code', $user);

    $t->Assign('g_code', $user['reset_code']);
    $t->AssignByRef('g_user', $user);

    $m = new Mailer();
    $result = $m->Mail('email-user-reset-confirm.tpl', $t, $user['email'], $user['name']);

    if( !$result) throw new BaseException($m->ErrorInfo);

    $t->Display('user-reset-found.tpl');
}

function tbxDisplayResetConfirm()
{
    global $t;

    $DB = GetDB();
    $v = Validator::Create();

    // Remove expired codes
    $DB->Update('DELETE FROM `tbx_user_reset_code` WHERE `timestamp` < ?', array(time() - 3600));

    $confirmation = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_reset_code` USING (`username`) WHERE `reset_code`=?', array($_REQUEST['code']));

    $v->Register(empty($confirmation), Validator_Type::IS_FALSE, _T('Validation:Invalid confirmation code'));

    if( !$v->Validate() )
    {
        $t->Assign('g_errors', $v->GetErrors());
    }
    else
    {
        $DB->Update('DELETE FROM `tbx_user_reset_code` WHERE `username`=?', array($confirmation['username']));
        $user = $DB->Row('SELECT * FROM `tbx_user` JOIN `tbx_user_custom` USING (`username`) JOIN `tbx_user_stat` USING (`username`) WHERE `tbx_user`.`username`=?', array($confirmation['username']));
        $password = RandomPassword();
        DatabaseUpdate('tbx_user', array('username' => $user['username'], 'password' => sha1($password)));

        $t->AssignByRef('g_user', $user);
        $t->Assign('g_password', $password);

        $m = new Mailer();
        $m->Mail('email-user-reset.tpl', $t, $user['email'], $user['name']);
    }

    $t->Display('user-reset-confirmed.tpl');
}


?>
