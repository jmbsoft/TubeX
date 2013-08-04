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

require_once realpath(dirname(__FILE__) . '/../') . '/classes/Config.php';

define('LIC_PRODUCT', 'TubeX');
define('LIC_DOMAIN', 'Any');
define('LIC_LICENSEE', '');
define('LIC_USERNAME', '');
define('LIC_POWEREDBY', false);

// Defines
define('STATUS_SUBMITTED', 'Submitted');
define('STATUS_PENDING', 'Pending');
define('STATUS_QUEUED', 'Queued');
define('STATUS_SCHEDULED', 'Scheduled');
define('STATUS_ACTIVE', 'Active');
define('STATUS_DISABLED', 'Disabled');

define('COMMENTS_NO', 'No');
define('COMMENTS_APPROVE', 'Yes - Require Approval');
define('COMMENTS_IMMEDIATE', 'Yes - Add Immediately');

define('DATE_FRIENDLY', 'M j, Y');
define('DATETIME_FRIENDLY', 'M j, Y g:ia');


// Set error handler
set_exception_handler('ExceptionHandler');
set_error_handler('ErrorHandler');


// Setup request values
Request::Setup();

function tbxAboutShow()
{
    Privileges::CheckSuper();


    $output = array();
    $link_removal = LIC_POWEREDBY == 'true' ? 'Not Purchased' : 'Purchased';
    $product = LIC_PRODUCT;
    $licensee = LIC_LICENSEE;
    $domain = LIC_DOMAIN;

    $output['html'] = <<<STUFF
    <div id="dialog-header" class="ui-widget-header ui-corner-all">
      <div id="dialog-close"></div>
      About $product
    </div>

    <div id="dialog-panel">
      <div style="padding: 8px;">
        <span style="font-size: 130%; font-weight: bold;">
          This is $product version 1.0.1 released on August 4th, 2013
        </span>

        <div class="field">
          <label>Licensee:</label>
          <span class="text-container">All</span>
        </div>

        <div class="field">
          <label>Licensed Domain:</label>
          <span class="text-container">$domain</span>
        </div>

        <div class="field">
          <label>Link Removal:</label>
          <span class="text-container">Yes</span>
        </div>

      </div>
    </div>

    <div id="dialog-buttons">
      <input type="button" id="dialog-button-cancel" value="Close" style="margin-left: 10px;" />
    </div>
STUFF;


    JSON::Success($output);
}

function DeleteVideo($video)
{
    $DB = GetDB();

    $DB->Update('DELETE FROM `tbx_video` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_clip` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_thumbnail` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_rating` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_flagged` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_featured` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_favorited` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_custom` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_stat` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_comment` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_video_private` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_user_favorite` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_conversion_queue` WHERE `video_id`=?', array($video['video_id']));
    $DB->Update('DELETE FROM `tbx_thumb_queue` WHERE `video_id`=?', array($video['video_id']));

    if( $video['status'] == STATUS_ACTIVE && !$video['is_private'] )
    {
        Tags::RemoveFromFrequency($video['tags']);
    }

    $video_dir = new Video_Dir(Video_Dir::DirNameFromId($video['video_id']));
    $video_dir->Remove();

    UpdateCategoryStats($video['category_id']);
    UpdateSponsorStats($video['sponsor_id']);

    $t = new Template();
    $t->ClearCache('video-watch.tpl', $video['video_id']);
}

function UpdateVideoCommentStats($comment, $removing = false)
{
    $date = preg_replace('~[^0-9]~', '', $comment['date_commented']);
    $op = $removing ? '-' : '+';
    $today_start = date('Ymd000000');
    $week_start = date('Ymd000000', date('w') == 0 ? strtotime('this sunday') : strtotime('last sunday'));
    $month_start = date('Ym01000000');

    $sets = array('`total_num_comments`=`total_num_comments`' . $op . '1');
    $user_sets = array('`total_comments_submitted`=`total_comments_submitted`' . $op . '1');
    if( $date >= $today_start )
    {
        $sets[] = '`today_num_comments`=`today_num_comments`' . $op . '1';
        $user_sets[] = '`today_comments_submitted`=`today_comments_submitted`' . $op . '1';
    }

    if( $date >= $week_start )
    {
        $sets[] = '`week_num_comments`=`week_num_comments`' . $op . '1';
        $user_sets[] = '`week_comments_submitted`=`week_comments_submitted`' . $op . '1';
    }


    if( $date >= $month_start )
    {
        $sets[] = '`month_num_comments`=`month_num_comments`' . $op . '1';
        $user_sets[] = '`month_comments_submitted`=`month_comments_submitted`' . $op . '1';
    }

    $DB = GetDB();

    $DB->Update(
        'UPDATE `tbx_video_stat` SET ' . join(',', $sets) . ' WHERE `video_id`=?',
        array(
            $comment['video_id']
        )
    );

    $DB->Update(
        'UPDATE `tbx_user_stat` SET ' . join(',', $sets) . ' WHERE `username`=?',
        array(
            $comment['username']
        )
    );
}

function UpdateCategoryStats()
{
    $DB = GetDB();

    $args = func_get_args();
    $ids = join(',', array_unique($args));
    $result = $DB->Query('SELECT `category_id` FROM `tbx_category`' . (empty($ids) ? '' : ' WHERE `category_id` IN ('.$ids.')'));

    while( $category = $DB->NextRow($result) )
    {
        $id = $category['category_id'];
        $last_video_id = $DB->QuerySingleColumn('SELECT `date_added` FROM `tbx_video` WHERE `category_id`=? AND `status`=? AND `is_private`=0 ORDER BY ? DESC LIMIT 1', array($id, STATUS_ACTIVE, 'date_added'));
        $num_videos = $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video` WHERE `category_id`=? AND `status`=? AND `is_private`=0', array($id, STATUS_ACTIVE));
        $DB->Update('UPDATE `tbx_category` SET `num_videos`=?,`last_video_id`=? WHERE `category_id`=?', array($num_videos, $last_video_id, $id));
    }

    $DB->Free($result);

    $t = new Template();
    $t->ClearCache('categories.tpl');
}

function UpdateSponsorStats()
{
    $DB = GetDB();

    $args = func_get_args();
    $ids = join(',', array_unique(array_diff($args, array(null, 0, ''))));

    if( empty($ids) && func_num_args() > 0 )
    {
        return;
    }

    $result = $DB->Query('SELECT `sponsor_id` FROM `tbx_sponsor`' . (empty($ids) ? '' : ' WHERE `sponsor_id` IN ('.$ids.')'));

    while( $sponsor = $DB->NextRow($result) )
    {
        $id = $sponsor['sponsor_id'];
        $videos = $DB->QueryCount('SELECT COUNT(*) FROM `tbx_video` WHERE `sponsor_id`=?', array($id));
        $DB->Update('UPDATE `tbx_sponsor` SET `videos`=? WHERE `sponsor_id`=?', array($videos, $id));
    }

    $DB->Free($result);
}

function GetBestCategory($search_data)
{
    if( Cache_Memory::IsCached(CACHE_CATEGORIES) )
    {
        $categories = Cache_Memory::Get(CACHE_CATEGORIES);
    }
    else
    {
        $DB = GetDB();
        $categories = $DB->FetchAll('SELECT * FROM `tbx_category`');
        Cache_Memory::Cache(CACHE_CATEGORIES, $categories);
    }

    $best_score = 0;
    $best_category_id = null;
    foreach( $categories as $category )
    {
        if( !String::IsEmpty($category['auto_category_term']) && preg_match('~(' . str_replace(',', '|', preg_quote($category['auto_category_term'])) . ')~i', $search_data, $matches) )
        {
            if( count($matches[1]) > $best_score )
            {
                $best_score = count($matches[1]);
                $best_category_id = $category['category_id'];
            }
        }
    }

    return $best_category_id;
}

function ResolvePath($path)
{
    $path = explode('/', str_replace('//', '/', $path));

    for( $i = 0; $i < count($path); $i++ )
    {
        if( $path[$i] == '.' )
        {
            unset($path[$i]);
            $path = array_values($path);
            $i--;
        }
        elseif( $path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '')) )
        {
            unset($path[$i]);
            unset($path[$i-1]);
            $path = array_values($path);
            $i -= 2;
        }
        elseif( $path[$i] == '..' AND $i == 1 AND $path[0] == '' )
        {
            unset($path[$i]);
            $path = array_values($path);
            $i--;
        }
        else
        {
            continue;
        }
    }

    return implode('/', $path);
}

function RelativeToAbsolute($start_url, $relative_url)
{
    if( preg_match('~^https?://~', $relative_url) )
    {
        return $relative_url;
    }

    $parsed = parse_url($start_url);
    $base_url = "{$parsed['scheme']}://{$parsed['host']}" . (isset($parsed['port']) ? ":{$parsed['port']}" : "");
    $path = $parsed['path'];

    if( $relative_url{0} == '/' )
    {
        return $base_url . ResolvePath($relative_url);
    }

    $path = preg_replace('~[^/]+$~', '', $path);

    return $base_url . ResolvePath($path . $relative_url);
}

function MergeTables($xtable, $original)
{
    $DB = GetDB();
    $schema = GetDBSchema();
    $primary_key = $xtable->columns->primaryKey->val();

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

    return $original;
}

function GetAgeInYears($birthday)
{
    list($b_year, $b_month, $b_day) = explode('-', $birthday);
    list($n_year, $n_month, $n_day) = explode('-', date('Y-m-d'));

    $age = $n_year - $b_year;
    $diff_month = $n_month - $b_month;
    $diff_day = $n_day - $b_day;

    // If birthday has not happen yet for this year, subtract 1.
    if ($diff_month < 0 || ($diff_month == 0 && $diff_day < 0))
    {
        $age--;
    }

    return $age;
}

function RandomPassword()
{
    // 1 symbol, 2 numbers, 3 uppercase, 4 lowercase
    $lc_letters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
    $uc_letters = array_map('strtoupper', $lc_letters);
    $numbers = array(0,1,2,3,4,5,6,7,8,9);
    $symbols = array('!','@','#','$','%','^','&','*','+','~','?');

    shuffle($lc_letters);
    shuffle($uc_letters);
    shuffle($numbers);
    shuffle($symbols);

    $password = array(array_pop($symbols),
                      array_pop($numbers),
                      array_pop($numbers),
                      array_pop($uc_letters),
                      array_pop($uc_letters),
                      array_pop($uc_letters),
                      array_pop($lc_letters),
                      array_pop($lc_letters),
                      array_pop($lc_letters),
                      array_pop($lc_letters));

    shuffle($password);

    return join('', $password);
}

function GetDBTables()
{
    $schema = GetDBSchema();
    $tables = array();

    foreach( $schema->xpath('//database/table') as $xtable )
    {
        $tables[] = $xtable->name->val();
    }

    return $tables;
}

function GetDBCreate($table)
{
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[name="' . $table . '"]');

    $columns = array();
    foreach( $xtable->xpath('./columns/column') as $xcolumn )
    {
        $columns[] = '`' . $xcolumn->name . '` ' . $xcolumn->definition;
    }


    $indexes = array();
    foreach( $xtable->xpath('./columns/index') as $xindex )
    {
        $index_columns = array();
        foreach( $xindex->column as $xcolumn )
        {
            preg_match('~^([a-z0-9_]+)(\(\d+\))?~', $xcolumn->val(), $matches);
            $index_columns[] = '`' . $matches[1] . '`' . (isset($matches[2]) ? $matches[2] : '');
        }
        $indexes[] = 'INDEX(' . join(',', $index_columns) . ')';
    }


    $uniques = array();
    foreach( $xtable->xpath('./columns/unique') as $xunique )
    {
        $unique_columns = array();
        foreach( $xunique->column as $xcolumn )
        {
            preg_match('~^([a-z0-9_]+)(\(\d+\))?~', $xcolumn->val(), $matches);
            $unique_columns[] = '`' . $matches[1] . '`' . (isset($matches[2]) ? $matches[2] : '');
        }
        $uniques[] = 'UNIQUE INDEX(' . join(',', $unique_columns) . ')';
    }


    $fulltexts = array();
    foreach( $xtable->xpath('./columns/fulltext') as $xfulltext )
    {
        $fulltext_columns = array();
        foreach( $xfulltext->column as $xcolumn )
        {
            $fulltext_columns[] = '`' . $xcolumn->val() . '`';
        }
        $fulltexts[] = 'FULLTEXT INDEX(' . join(',', $fulltext_columns) . ')';
    }

    return "CREATE TABLE IF NOT EXISTS `" . $table . "` (\n" .
           join(",\n", $columns) .
           (count($indexes) > 0 ? ",\n" . join(",\n", $indexes) : '') .
           (count($uniques) > 0 ? ",\n" . join(",\n", $uniques) : '') .
           (count($fulltexts) > 0 ? ",\n" . join(",\n", $fulltexts) : '') .
           ') ENGINE=MyISAM DEFAULT CHARSET=utf8';
}

function DatabaseAdd($table, $data)
{
    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[name="'.$table.'"]');
    $primary_key = $xtable->columns->primaryKey->val();
    $xpkey_column = $xtable->el('./columns/column[name="'.$primary_key.'"]');

    $binds = array($table);
    $placeholders = 0;
    foreach( $xtable->xpath('./columns/column') as $xcolumn )
    {
        $field = $xcolumn->name->val();
        $binds[] = isset($data[$field]) ? $data[$field] : $xcolumn->default->val();
        $placeholders++;
    }

    $DB->Update('INSERT INTO # VALUES (' . join(',', array_fill(0, $placeholders, '?')) . ')', $binds);

    if( strpos($xpkey_column->definition, 'AUTO_INCREMENT') === false )
    {
        return $data[$primary_key];
    }
    else
    {
        return $DB->LastInsertId();
    }
}

function DatabaseUpdate($table, $data)
{
    $DB = GetDB();
    $schema = GetDBSchema();
    $xtable = $schema->el('//table[name="'.$table.'"]');
    $primary_key = $xtable->columns->primaryKey->val();
    $xpkey_column = $xtable->el('./columns/column[name="'.$primary_key.'"]');

    $binds = array($table);
    $placeholders = 0;
    foreach( $xtable->xpath('./columns/column') as $xcolumn )
    {
        $field = $xcolumn->name->val();

        if( array_key_exists($field, $data) )
        {
            $binds[] = $field;
            $binds[] = $data[$field];
            $placeholders++;
        }
    }

    $binds[] = $primary_key;
    $binds[] = $data[$primary_key];

    if( $placeholders > 0 )
    {
        $DB->Update('UPDATE # SET ' . join(',', array_fill(0, $placeholders, '#=?')) . ' WHERE #=?', $binds);
    }

    return $DB->Row('SELECT * FROM # WHERE #=?', array($table, $primary_key, $data[$primary_key]));
}

function GetDBSchema($force = false)
{
    static $xml;

    if( !isset($xml) || $force )
    {
        $xml = simplexml_load_file(BASE_DIR.'/includes/database.xml', 'XML_Element');
    }

    return $xml;
}

function NoCacheHeaders()
{
    header('Expires: Mon, 26 Jul 1990 05:00:00 GMT');
    header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
}

function ErrorHandler($code, $string)
{
    $reporting = error_reporting();

    if( $reporting == 0 || !($code & $reporting) )
    {
        return;
    }

    throw new BaseException('A non-recoverable program error has occurred', $string);
}

function ExceptionHandler($e)
{
    $internal_error = null;
    $message = $e->getMessage();
    $log_message = strip_tags("[" . date('m-d-Y h:i:sa') . "] " . $message . (strtolower(get_class($e)) == 'baseexception' ? $e->getExtras() : ''). "\n" .
                   "\tStack Trace:\n" . preg_replace('~^~m', "\t\t", $e->getTraceAsString())) . "\n\n";

    // Attempt to write information to the error log
    try
    {
        $fp = fopen(BASE_DIR . '/data/error_log', 'a+');
        fwrite($fp, $log_message);
        fclose($fp);
    }
    catch(Exception $e)
    {
        $internal_error = "Could not append data to the error log!\n";
    }


    // Display the error message
    if( PHP_SAPI == 'cli' || defined('TUBEX_AJAX') )
    {
        echo "Error: $message\n";
        echo "\nLog Information\n" .
             "===============\n" .
             "$log_message\n" .
             $internal_error;
    }
    else
    {
        echo "<html>\n".
             "<head>\n".
             "<title>Error</title>\n".
             "<style>\n".
             "body { \n".
             "  font-family: 'Trebuchet MS', Arial, Helvetica;\n".
             "  font-size: 13pt;\n".
             "}\n".
             "h1 {\n".
             "  color: red;\n".
             "  font-size: 18pt;\n".
             "  font-weight: bold;\n".
             "  margin: 0;\n".
             "  padding: 0;\n".
             "}\n".
             "xmp {\n".
             "  font-size: 9pt;\n".
             "  margin: 0;\n".
             "  padding: 0;\n".
             "}\n".
             "</style>\n".
             "<body>\n".
             "<div style=\"text-align: center\">\n".
             "<h1>An Error Has Occurred</h1>\n".
             "<div style=\"margin-bottom: 10px;\">\n".
             nl2br($message) . "<br />\n".
             "<div style=\"font-size: 10pt;\">The site administrator has been notified of this issue and will look into it as soon as possible</div>\n".
             $internal_error .
             "</div>\n".
             "</div>\n";

        if( defined('TUBEX_CONTROL_PANEL') )
        {
            echo "<b>Log Information</b><br />\n".
                 "<xmp>" . $log_message . "</xmp>\n";
        }

        echo "</body>\n".
             "</html>\n";
    }

    exit;
}


?>
