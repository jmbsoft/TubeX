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

$path = realpath(dirname(__FILE__));
chdir($path);

define('TUBEX_CONTROL_PANEL', true);

require_once('includes/cp-global.php');

switch($GLOBALS['argv'][1])
{
    case '--backup':
        $args = ParseCommandLine();

        if( !isset($args['file']) || empty($args['file']) )
        {
            echo "Please specify a filename using the --file option\n";
            exit;
        }

        $DB = GetDB();
        $tables = GetDBTables();
        $DB->DumpTables($tables, $args['file']);
        break;


    case '--restore':
        $args = ParseCommandLine();

        if( !isset($args['file']) || empty($args['file']) )
        {
            echo "Please specify a filename using the --file option\n";
            exit;
        }

        $DB = GetDB();
        $DB->RestoreTables($args['file']);
        break;


    case '--feed-read':
        ReadFromFields();
        break;


    case '--stats-rollover':
        StatsRollover(true);
        break;


    case '--process-queue':
    case '--run-convert-queue':
        ConversionQueue::Start();
        break;


    case '--run-thumb-queue':
        ThumbQueue::Start();
        break;


    case '--activate-scheduled':
        ActivateScheduledVideos();
        break;
}

function ActivateScheduledVideos()
{
    $DB = GetDB();
    $args = ParseCommandLine();
    $queries = array();

    if( !isset($args['sort']) || empty($args['sort']) )
    {
        $args['sort'] = 'RAND()';
    }


    if( !isset($args['sort-direction']) || empty($args['sort-direction']) )
    {
        $args['sort-direction'] = SQL::SORT_ASC;
    }

    if( isset($args['amount']) )
    {
        $sb = new SQL_SelectBuilder('tbx_video');
        $sb->AddSelectField('`video_id`');
        $sb->AddSelectField('`tags`');
        $sb->AddWhere('tbx_video.status', SQL::EQUALS, STATUS_SCHEDULED);
        $sb->AddOrder($args['sort'], $args['sort-direction']);
        $sb->SetLimit($args['amount']);

        $queries[] = $DB->Prepare($sb->Generate(), $sb->Binds());
    }
    else if( isset($args['amount-per-sponsor']) )
    {
        $result = $DB->Query('SELECT `sponsor_id` FROM `tbx_sponsor`');

        while( $sponsor = $DB->NextRow($result) )
        {
            $sb = new SQL_SelectBuilder('tbx_video');
            $sb->AddSelectField('`video_id`');
            $sb->AddSelectField('`tags`');
            $sb->AddWhere('tbx_sponsor.sponsor_id', SQL::EQUALS, $sponsor['sponsor_id']);
            $sb->AddWhere('tbx_video.status', SQL::EQUALS, STATUS_SCHEDULED);
            $sb->AddOrder($args['sort'], $args['sort-direction']);
            $sb->SetLimit($args['amount-per-sponsor']);
            $queries[] = $DB->Prepare($sb->Generate(), $sb->Binds());
        }

        $DB->Free($result);
    }
    else if( isset($args['amount-per-category']) )
    {
        $result = $DB->Query('SELECT `category_id` FROM `tbx_category`');

        while( $category = $DB->NextRow($result) )
        {
            $sb = new SQL_SelectBuilder('tbx_video');
            $sb->AddSelectField('`video_id`');
            $sb->AddSelectField('`tags`');
            $sb->AddWhere('tbx_video.category_id', SQL::EQUALS, $category['category_id']);
            $sb->AddWhere('tbx_video.status', SQL::EQUALS, STATUS_SCHEDULED);
            $sb->AddOrder($args['sort'], $args['sort-direction']);
            $sb->SetLimit($args['amount-per-category']);
            $queries[] = $DB->Prepare($sb->Generate(), $sb->Binds());
        }

        $DB->Free($result);
    }
    else
    {
        throw new BaseException('One of --amount, --amount-per-sponsor or --amount-per-category must be specified');
    }

    foreach( $queries as $query )
    {
        $result = $DB->Query($query);

        while( $video = $DB->NextRow($result) )
        {
            $DB->Update('UPDATE `tbx_video` SET `status`=?,`date_added`=? WHERE `video_id`=?', array(STATUS_ACTIVE, Database_MySQL::Now(), $video['video_id']));
            Tags::AddToFrequency($video['tags']);
        }

        $DB->Free($result);
    }

    UpdateCategoryStats();
}

function ReadFromFields()
{
    $args = ParseCommandLine();

    $query = 'SELECT * FROM `tbx_video_feed`';
    if( isset($args['feeds']) )
    {
        $ids = array();
        foreach( explode(',', $args['feeds']) as $id )
        {
            $id = trim($id);
            $range = explode('-', $id);

            if( count($range) == 2 )
            {
                $ids = array_merge($ids, range($range[0], $range[1]));
            }
            else
            {
                $ids[] = $id;
            }
        }

        $ids = preg_grep('~^\d+$~', $ids);

        if( !empty($ids) )
        {
            $query .= ' WHERE `feed_id` IN (' . join(',', $ids) . ')';
        }
    }

    $DB = GetDB();

    $result = $DB->Query($query);

    while( $feed = $DB->NextRow($result) )
    {
        $vf = Video_Feed::Create($feed);
        $imported = $vf->Import();
    }

    $DB->Free($result);
}

function ParseCommandLine()
{
    $args = array();

    foreach( $GLOBALS['argv'] as $arg )
    {
        // Check if this is a valid argument in --ARG or --ARG=SOMETHING format
        if( preg_match('~--([a-z0-9\-_]+)(=?)(.*)?~i', $arg, $matches) )
        {
            if( $matches[2] == '=' )
            {
                $args[$matches[1]] = $matches[3];
            }
            else
            {
                $args[$matches[1]] = true;
            }

        }
    }

    return $args;
}


?>