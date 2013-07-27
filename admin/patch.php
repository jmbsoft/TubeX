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


$DB = GetDB();





#### Update config.php file =======================================================================================================
// Update Config.php file to remove PHP_INT_MAX reference
$config = file_get_contents(BASE_DIR . '/classes/Config.php');
$config = String::FormatNewlines($config, String::NEWLINE_UNIX);
$config = str_replace(array('fread($fp, PHP_INT_MAX)', 'fread($fp, 1048576)'), 'fgets($fp)', $config);

if( stristr($config, 'Patched TEMPLATES_DIR') === false )
{
    $config = str_replace("define('VIDEO_EXTENSIONS', str_replace(array('.',','), array('', '|'), self::\$settings['video_extensions']));",
                          "define('VIDEO_EXTENSIONS', str_replace(array('.',','), array('', '|'), self::\$settings['video_extensions']));\n" .
                          "        define('TEMPLATES_DIR', BASE_DIR . '/templates/' . self::\$settings['template']); // Patched TEMPLATES_DIR\n",
                          $config);
}


if( stristr($config, 'TEMPLATE_COMPILE_DIR') === false )
{
    $config = str_replace("define('TEMPLATES_DIR', BASE_DIR . '/templates');",
                          "define('TEMPLATE_CACHE_DIR', BASE_DIR . '/templates/_cache');\ndefine('TEMPLATE_COMPILE_DIR', BASE_DIR . '/templates/_compiled');",
                          $config);
}


if( stristr($config, 'spl_autoload_register') === false )
{
    $config = str_replace("function __autoload(\$class)\n" .
"{\n" .
"    \$filename = str_replace('_', '/', \$class) . '.php';\n" .
"    require_once(\$filename);\n" .
"}",
"if( function_exists('spl_autoload_register') )\n" .
"{\n" .
"    function JMBAutoload(\$class)\n" .
"    {\n" .
"        \$filename = str_replace('_', '/', \$class) . '.php';\n" .
"        require_once(\$filename);\n" .
"    }\n" .
"\n" .
"    spl_autoload_register('JMBAutoload');\n" .
"}\n" .
"else\n" .
"{\n" .
"    function __autoload(\$class)\n" .
"    {\n" .
"        \$filename = str_replace('_', '/', \$class) . '.php';\n" .
"        require_once(\$filename);\n" .
"    }\n" .
"}",
                          $config);
}

file_put_contents(BASE_DIR . '/classes/Config.php', $config);
#### Update config.php file =======================================================================================================







#### Update database.xml ==========================================================================================================
// Update tbx_category with image_id column
$schema = GetDBSchema(true);
$xcolumns = $schema->el('//table[name="tbx_category"]/columns');
if( $xcolumns->el('./column[name="image_id"]') === null )
{
    // Setup <column>
    $xcolumn = $xcolumns->addChild('column');
    $xcolumn->addChild('name', 'image_id');
    $xcolumn->addChild('label', 'Image');
    $xcolumn->addChild('definition', 'INT UNSIGNED');
    $xcolumn->addChild('description', 'An image associated with the category');


    // Setup <admin>
    $xadmin = $xcolumn->addChild('admin');
    $xadmin->addChild('search', 'false');
    $xadmin->addChild('sort', 'false');
    $xadmin->addChild('create', 'true');
    $xadmin->addChild('edit', 'true');

    XML_Schema::WriteXml($schema);
}


// Update tbx_video with bulk editing for the duration field
$schema = GetDBSchema(true);
$xcolumns = $schema->el('//table[name="tbx_video"]/columns');
if( $xcolumns->el('./column[name="duration"]/admin/bulkEdit') === null )
{
    $xadmin = $xcolumns->el('./column[name="duration"]/admin');
    $xadmin->addChild('bulkEdit', 'SET,ADD,SUBTRACT,INCREMENT,DECREMENT');

    XML_Schema::WriteXml($schema);
}



// Update tbx_video_feed with flag_convert column
$schema = GetDBSchema(true);
$xcolumns = $schema->el('//table[name="tbx_video_feed"]/columns');
if( $xcolumns->el('./column[name="flag_convert"]') === null )
{
    // Setup <column>
    $xcolumn = $xcolumns->addChild('column');
    $xcolumn->addChild('name', 'flag_convert');
    $xcolumn->addChild('label', 'Queue for conversion');
    $xcolumn->addChild('definition', 'TINYINT UNSIGNED NOT NULL');
    $xcolumn->addChild('description', 'Queue videos imported from this feed for conversion');
    $xcolumn->addChild('default', '0');
    $xcolumn->addChild('autocomplete', '#Yes,No');


    // Setup <admin>
    $xadmin = $xcolumn->addChild('admin');
    $xadmin->addChild('search', 'false');
    $xadmin->addChild('sort', 'false');
    $xadmin->addChild('create', 'true');
    $xadmin->addChild('edit', 'true');
    $xadmin->addChild('bulkEdit', 'SET');

    XML_Schema::WriteXml($schema);
}

// Update tbx_video_feed with flag_thumb column
$schema = GetDBSchema(true);
$xcolumns = $schema->el('//table[name="tbx_video_feed"]/columns');
if( $xcolumns->el('./column[name="flag_thumb"]') === null )
{
    // Setup <column>
    $xcolumn = $xcolumns->addChild('column');
    $xcolumn->addChild('name', 'flag_thumb');
    $xcolumn->addChild('label', 'Queue for thumbnail generation');
    $xcolumn->addChild('definition', 'TINYINT UNSIGNED NOT NULL');
    $xcolumn->addChild('description', 'Queue videos imported from this feed for thumbnail generation');
    $xcolumn->addChild('default', '0');
    $xcolumn->addChild('autocomplete', '#Yes,No');


    // Setup <admin>
    $xadmin = $xcolumn->addChild('admin');
    $xadmin->addChild('search', 'false');
    $xadmin->addChild('sort', 'false');
    $xadmin->addChild('create', 'true');
    $xadmin->addChild('edit', 'true');
    $xadmin->addChild('bulkEdit', 'SET');

    XML_Schema::WriteXml($schema);
}

$database_xml = file_get_contents(INCLUDES_DIR . '/database.xml');

// Point to correct documentation locations
$database_xml = str_replace('custom.html<', 'custom-field.html<', $database_xml);


// Add next_status to tbx_video
if( strpos($database_xml, 'next_status') === false )
{
    $database_xml = str_replace("<column>\n        <name>duration</name>", "<column>\n" .
"        <name>next_status</name>\n" .
"        <definition>ENUM('Pending','Queued','Scheduled','Active','Disabled')</definition>\n" .
"        <default>null</default>\n" .
"        <user>\n" .
"          <create>false</create>\n" .
"          <edit>false</edit>\n" .
"        </user>\n" .
"        <admin>\n" .
"          <search>false</search>\n" .
"          <sort>false</sort>\n" .
"          <create>false</create>\n" .
"          <edit>false</edit>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>duration</name>", $database_xml);
}


// Add convert and thumbnail toolbar icons
if( strpos($database_xml, 'conversion-queue-32x32.png') === false )
{
    $database_xml = str_replace("<function>tbxGenericAction(video,unfeature)</function>\n      </icon>", "<function>tbxGenericAction(video,unfeature)</function>\n" .
"      </icon>\n" .
"      <icon>\n" .
"        <type>action</type>\n" .
"        <img>conversion-queue-32x32.png</img>\n" .
"        <title>Convert</title>\n" .
"        <function>tbxGenericAction(video,convert)</function>\n" .
"      </icon>\n" .
"      <icon>\n" .
"        <type>action</type>\n" .
"        <img>thumb-queue-32x32.png</img>\n" .
"        <title>Thumbnail</title>\n" .
"        <function>tbxGenericAction(video,thumbnail)</function>\n" .
"      </icon>", $database_xml);
}


// Add tbx_imported table definition
if( strpos($database_xml, 'tbx_imported') === false )
{
    $database_xml = str_replace('</database>', "\n\n\n<!-- START tbx_imported -->\n" .
"  <table>\n" .
"    <name>tbx_imported</name>\n" .
"    <naming>\n" .
"      <type>imported</type>\n" .
"    </naming>\n" .
"    <columns>\n" .
"      <column>\n" .
"        <name>video_url</name>\n" .
"        <definition>TEXT</definition>\n" .
"      </column>\n" .
"      <index>\n" .
"        <column>video_url(255)</column>\n" .
"      </index>\n" .
"    </columns>\n" .
"  </table>\n" .
"<!-- END tbx_imported -->\n" .
"</database>", $database_xml);
}


// Add tbx_thumb_queue table definition
if( strpos($database_xml, 'tbx_thumb_queue') === false )
{
    $database_xml = str_replace('<!-- END tbx_conversion_queue -->', "<!-- END tbx_conversion_queue -->\n\n\n\n" .
"<!-- START tbx_thumb_queue -->\n" .
"  <table>\n" .
"    <name>tbx_thumb_queue</name>\n" .
"    <naming>\n" .
"      <type>thumb-queue</type>\n" .
"    </naming>\n" .
"    <columns>\n" .
"      <column>\n" .
"        <name>video_id</name>\n" .
"        <definition>INT UNSIGNED NOT NULL PRIMARY KEY</definition>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>queued</name>\n" .
"        <definition>INT NOT NULL</definition>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>date_started</name>\n" .
"        <definition>DATETIME</definition>\n" .
"        <default>null</default>\n" .
"      </column>\n" .
"      <primaryKey>video_id</primaryKey>\n" .
"      <index>\n" .
"        <column>queued</column>\n" .
"      </index>\n" .
"    </columns>\n" .
"  </table>\n" .
"<!-- END tbx_thumb_queue -->", $database_xml);
}


// Add tbx_search_term_new table definition
if( strpos($database_xml, 'tbx_search_term_new') === false )
{
    $database_xml = str_replace('<!-- END tbx_search_term -->', "<!-- END tbx_search_term -->\n\n\n\n" .
"<!-- START tbx_search_term_new -->\n" .
"  <table>\n" .
"    <name>tbx_search_term_new</name>\n" .
"    <naming>\n" .
"      <type>search-term-new</type>\n" .
"    </naming>\n" .
"    <columns>\n" .
"      <column>\n" .
"        <name>term_id</name>\n" .
"        <definition>INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT</definition>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>term</name>\n" .
"        <definition>VARCHAR(255)</definition>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>frequency</name>\n" .
"        <definition>INT UNSIGNED NOT NULL</definition>\n" .
"      </column>\n" .
"      <primaryKey>term_id</primaryKey>\n" .
"      <unique>\n" .
"        <column>term</column>\n" .
"      </unique>\n" .
"    </columns>\n" .
"  </table>\n" .
"<!-- END tbx_search_term_new -->", $database_xml);
}


// Add join on the tbx_video_clip table
// Add tbx_search_term_new table definition
if( strpos($database_xml, "<join>\n      <table>tbx_video_clip</table>") === false )
{
    $database_xml = str_replace("<join>\n" .
"      <table>tbx_video_custom</table>\n" .
"      <foreign>video_id</foreign>\n" .
"      <local>video_id</local>\n" .
"    </join>",
"<join>\n" .
"      <table>tbx_video_custom</table>\n" .
"      <foreign>video_id</foreign>\n" .
"      <local>video_id</local>\n" .
"    </join>\n" .
"    <join>\n" .
"      <table>tbx_video_clip</table>\n" .
"      <foreign>video_id</foreign>\n" .
"      <local>video_id</local>\n" .
"    </join>",
    $database_xml);
}


if( strpos($database_xml, 'Clip URL/Embed Code') === false )
{
    $database_xml = preg_replace('~<!-- START tbx_video_clip -->.*?<!-- END tbx_video_clip -->~msi',
"<!-- START tbx_video_clip -->\n" .
"  <table>\n" .
"    <name>tbx_video_clip</name>\n" .
"    <naming>\n" .
"      <type>video-clip</type>\n" .
"      <textLower>video clip</textLower>\n" .
"      <textLowerPlural>video clips</textLowerPlural>\n" .
"      <textUpper>Video Clip</textUpper>\n" .
"      <textUpperPlural>Video Clips</textUpperPlural>\n" .
"      <function>VideoClip</function>\n" .
"    </naming>\n" .
"    <columns>\n" .
"      <column>\n" .
"        <name>clip_id</name>\n" .
"        <definition>INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT</definition>\n" .
"        <default>null</default>\n" .
"        <admin>\n" .
"          <search>false</search>\n" .
"          <sort>false</sort>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>video_id</name>\n" .
"        <definition>INT UNSIGNED NOT NULL</definition>\n" .
"        <admin>\n" .
"          <search>false</search>\n" .
"          <sort>false</sort>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>type</name>\n" .
"        <definition>ENUM('URL','Embed')</definition>\n" .
"        <default>URL</default>\n" .
"        <admin>\n" .
"          <search>false</search>\n" .
"          <sort>false</sort>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>clip</name>\n" .
"        <definition>TEXT</definition>\n" .
"        <label>Clip URL/Embed Code</label>\n" .
"        <admin>\n" .
"          <search>true</search>\n" .
"          <sort>false</sort>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <column>\n" .
"        <name>filesize</name>\n" .
"        <definition>INT UNSIGNED NOT NULL</definition>\n" .
"        <default>0</default>\n" .
"        <admin>\n" .
"          <search>false</search>\n" .
"          <sort>false</sort>\n" .
"        </admin>\n" .
"      </column>\n" .
"      <index>\n" .
"        <column>video_id</column>\n" .
"      </index>\n" .
"      <primaryKey>clip_id</primaryKey>\n" .
"    </columns>\n" .
"  </table>\n" .
"  <!-- END tbx_video_clip -->",
    $database_xml);
}


$database_xml = str_replace("<definition>ENUM('Pending','Active','Disabled') NOT NULL</definition>", "<definition>ENUM('Pending','Scheduled','Active','Disabled') NOT NULL</definition>", $database_xml);
$database_xml = str_replace('<autocomplete>#Pending,Active,Disabled</autocomplete>', '<autocomplete>#Pending,Scheduled,Active,Disabled</autocomplete>', $database_xml);


file_put_contents(INCLUDES_DIR . '/database.xml', $database_xml);

// Force a reload of the schema
$schema = GetDBSchema(true);
#### Update database.xml ==========================================================================================================





#### Create database tables =======================================================================================================
// Create tbx_imported if doesn't already exist
$DB->Update(GetDBCreate('tbx_imported'));

// Create tbx_thumb_queue if doesn't already exist
$DB->Update(GetDBCreate('tbx_thumb_queue'));

// Create tbx_search_term_new if doesn't already exist
$DB->Update(GetDBCreate('tbx_search_term_new'));
#### Create database tables =======================================================================================================






#### Update database ==============================================================================================================
// Prepare for new QueueProcessor stats format
$stats = ThumbQueue::LoadStats();
if( !isset($stats[ThumbQueue::STAT_PROCESSED_ITEMS]) )
{
    Cache_MySQL::Remove('thumb-queue-stats');
}

// Prepare for new QueueProcessor stats format
$stats = ConversionQueue::LoadStats();
if( !isset($stats[ConversionQueue::STAT_PROCESSED_ITEMS]) )
{
    Cache_MySQL::Remove('conversion-queue-stats');
}


// Update sponsor video counts
if( $DB->QuerySingleColumn('SELECT MAX(`videos`) FROM `tbx_sponsor`') == 0 )
{
    UpdateSponsorStats();
}


// Update status in tbx_video_feed
$DB->Update("ALTER TABLE `tbx_video_feed` MODIFY `status` ENUM('Pending','Scheduled','Active','Disabled') NOT NULL");


// Add image_id to tbx_category
$columns = $DB->GetColumns('tbx_category');
if( !in_array('image_id', $columns) )
{
    $DB->Update('ALTER TABLE `tbx_category` ADD COLUMN `image_id` INT UNSIGNED');
}


// Add image_id to tbx_video_feed
$columns = $DB->GetColumns('tbx_video_feed');
if( !in_array('flag_convert', $columns) )
{
    $DB->Update('ALTER TABLE `tbx_video_feed` ADD COLUMN `flag_convert` TINYINT UNSIGNED NOT NULL');
}


// Add image_id to tbx_video_feed
if( !in_array('flag_thumb', $columns) )
{
    $DB->Update('ALTER TABLE `tbx_video_feed` ADD COLUMN `flag_thumb` TINYINT UNSIGNED NOT NULL');
}


// Add next_status to tbx_video
$columns = $DB->GetColumns('tbx_video');
if( !in_array('next_status', $columns) )
{
    $DB->Update("ALTER TABLE `tbx_video` ADD COLUMN `next_status` ENUM('Pending','Queued','Scheduled','Active','Disabled') AFTER `status`");
}

// Update available video statuses
$status_def = $DB->Row('DESCRIBE `tbx_video` `status`');
if( strpos($status_def['Type'], STATUS_SCHEDULED) === false )
{
    $DB->Update("ALTER TABLE `tbx_video` MODIFY COLUMN `status` ENUM('Pending','Queued','Scheduled','Active','Disabled') NOT NULL");
}
#### Update database ==============================================================================================================


echo "PATCHING HAS BEEN COMPLETED SUCCESSFULLY!";

?>