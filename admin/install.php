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
require_once 'includes/cp-global.php';



if( Config::Get('db_username') !== null )
{
    include_once 'install-already-installed.php';
}
else
{
    if( isset($_REQUEST['db_username']) )
    {
        if( DatabaseTest() )
        {
            $control_panel_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/index.php";
            $password = InitializeTables();
            Config::Save($_REQUEST);

            TemplateRecompileAll();

            include_once 'install-complete.php';

        }
    }
    else if( PreTest() )
    {
        $_REQUEST['db_hostname'] = 'localhost';
        include_once 'install-main.php';
    }
}

function PreTest()
{
    $errors = array();

    // Test template file permissions
    foreach( glob(TEMPLATES_DIR . '/*.*') as $filename )
    {
        if( !is_writeable($filename) )
        {
            $errors[] = "Template file $filename has incorrect permissions; change to 666";
        }
    }


    // Test other file permissions
    $files = array(BASE_DIR . '/classes/Config.php', INCLUDES_DIR . '/database.xml', INCLUDES_DIR . '/vp6.mcf');
    foreach( $files as $file )
    {
        if( !is_file($file) )
        {
            $errors[] = "File $file is missing; please upload this file and set permissions to 666";
        }
        else if( !is_writeable($file) )
        {
            $errors[] = "File $file has incorrect permissions; change to 666";
        }
    }


    // Test directory existence and permissions
    $dirs = array(array('dir' => TEMPLATE_CACHE_DIR, 'writeable' => true),
                  array('dir' => TEMPLATE_COMPILE_DIR, 'writeable' => true),
                  array('dir' => DATA_DIR, 'writeable' => true),
                  array('dir' => UPLOADS_DIR, 'writeable' => true),
                  array('dir' => VIDEOS_DIR, 'writeable' => true),
                  array('dir' => TEMP_DIR, 'writeable' => true));

    foreach( $dirs as $dir )
    {
        if( !is_dir($dir['dir']) )
        {
            $errors[] = "Directory " . $dir['dir'] . " is missing; please create this directory" . ($dir['writeable'] ? " and set permissions to 777" : '');
        }
        else if( $dir['writeable'] && !is_writeable($dir['dir']) )
        {
            $errors[] = "Directory " . $dir['dir'] . " has incorrect permissions; change to 777";
        }
    }


    if( count($errors) )
    {
        include_once 'install-pretest-fail.php';
        return false;
    }

    return true;
}

function DatabaseTest()
{
    global $DB;

    try
    {
        $DB = new Database_MySQL(Request::Get('db_username'), Request::Get('db_password'), Request::Get('db_database'), Request::Get('db_hostname'));
        $version = $DB->Version();

        if( !($version['major'] > 4 || ($version['major'] == 4 && $version['minor'] > 0)) )
        {
            throw new BaseException('This software requires MySQL Server version 4.1.0 or newer', 'Your MySQL Server is version ' . $version['full']);
        }
    }
    catch(Exception $e)
    {
        include_once 'install-main.php';
        return false;
    }

    return true;
}

function InitializeTables()
{
    global $DB;

    $tables = GetDBTables();
    foreach( $tables as $table )
    {
        $create = GetDBCreate($table);
        $DB->Update($create);
    }

    $password = RandomPassword();

    $DB->Update('DELETE FROM `tbx_administrator` WHERE `username`=?', array('administrator'));
    $DB->Update('INSERT INTO `tbx_administrator` VALUES (?,?,?,?,?,?)',
                array('administrator',
                      sha1($password),
                      'webmaster@' . preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST']),
                      'Administrator',
                      'Superuser',
                      0));

    $DB->Update('DELETE FROM `tbx_user_level` WHERE `name` IN (?,?)', array('Guest','Standard'));
    $DB->Update('INSERT INTO `tbx_user_level` VALUES (NULL,?,0,0,1,0)', array('Guest'));
    $DB->Update('INSERT INTO `tbx_user_level` VALUES (NULL,?,0,0,0,1)', array('Standard'));

    return $password;
}

?>