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
header("Content-type: text/html; charset: UTF-8");
NoCacheHeaders();

// Check for Firefox
$is_firefox = preg_match('~Firefox/~', $_SERVER['HTTP_USER_AGENT']);

if( Authenticate::Login() )
{
    Execute(Request::Get('r'), 'tbxIndexShow');
}
else
{
    Growl::AddError(Authenticate::GetError());
    include_once('cp-login.php');
}

function tbxVideoImportShow($errors = null)
{
    include_once('cp-video-import.php');
}

function tbxVideoImportAnalyze()
{
    $v = Validator::Create();

    try
    {
        $file = Video_Import::ProcessSource($_REQUEST);
        $fields = Video_Import::Analyze(TEMP_DIR . '/' . $file, Request::Get('delimiter'));
    }
    catch(Exception $e)
    {
        $v->SetError($e->getMessage());
    }

    if( !$v->Validate() )
    {
        return tbxVideoImportShow($v->GetErrors());
    }



    include_once('cp-video-import-analyze.php');
}

function tbxVideoImport()
{


    Video_Import::Import($_REQUEST);
}

function tbxVideoPlayer()
{
    $DB = GetDB();

    $video = $DB->Row('SELECT * FROM `tbx_video` WHERE `video_id`=?', array(Request::Get('video_id')));

    if( !empty($video) )
    {
        $clips = $DB->FetchAll('SELECT * FROM `tbx_video_clip` WHERE `video_id`=? ORDER BY `clip_id`', array($video['video_id']));
        include_once('cp-video-player.php');
    }
}

function tbxBannerDisplay()
{
    $DB = GetDB();
    $banner = $DB->Row('SELECT * FROM `tbx_banner` WHERE `banner_id`=?', array(Request::Get('id')));
    echo "<html><body style=\"margin: 0; padding: 0;\">" . $banner['banner_html'] . "</body></html>";
}

function tbxEmailTemplateShow()
{
    Privileges::Check(Privileges::TEMPLATES);
    include_once('cp-email-template.php');
}

function tbxSiteTemplateShow()
{
    Privileges::Check(Privileges::TEMPLATES);
    include_once('cp-site-template.php');
}

function tbxDatabaseUtilitiesShow()
{
    Privileges::Check(Privileges::DATABASE);
    include_once('cp-database-utilities.php');
}

function tbxDatabaseRepair()
{
    Privileges::Check(Privileges::DATABASE);

    $DB = GetDB();
    $tables = GetDBTables();
    $num_tables = count($tables);
    $counter = 0;

    ProgressBarShow('pb-repair');
    foreach( $tables as $table )
    {
        $DB->Query('REPAIR TABLE #', array($table));
        ProgressBarUpdate('pb-repair', ++$counter / $num_tables * 100, "Repairing $table...");
    }

    ProgressBarHide('pb-repair', 'Database repair has been completed!', 'b-repair');
}

function tbxDatabaseOptimize()
{
    Privileges::Check(Privileges::DATABASE);

    $DB = GetDB();
    $tables = GetDBTables();
    $num_tables = count($tables);
    $counter = 0;

    ProgressBarShow('pb-optimize');
    foreach( $tables as $table )
    {
        $DB->Query('OPTIMIZE TABLE #', array($table));
        ProgressBarUpdate('pb-optimize', ++$counter / $num_tables * 100, "Repairing $table...");
    }

    ProgressBarHide('pb-optimize', 'Database optimize has been completed!', 'b-optimize');
}

function tbxIndexShow()
{
    include_once('cp-index.php');
}

function tbxGenericShowSearch($type)
{
    Privileges::Check(Privileges::FromType($type));

    if( $type == 'search-term' )
    {
        Blacklist::FilterSearchTerms();
    }

    $schema = GetDBSchema();
    $table = $schema->el('//database/table[naming/type="'.$type.'"]')->name->val();
    include_once('cp-global-search.php');
}

function tbxLogout()
{
    global $is_firefox;

    Authenticate::Logout();
    Growl::AddMessage('You have been successfully logged out of the control panel');
    include_once('cp-login.php');
}


?>
