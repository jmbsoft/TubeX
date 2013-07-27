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

if( !preg_match('~/admin$~', realpath(dirname(__FILE__))) )
{
    echo "This file must be located in the admin directory of your TubeX installation";
    exit;
}

define('TUBEX_CONTROL_PANEL', true);

require_once('includes/cp-global.php');

if( $_SERVER['REQUEST_METHOD'] == 'POST' )
{
    $DB = GetDB();
    $tables = GetDBTables();
    $reset = true;
    $settings = array('cookie_path' => '/',
                      'dec_point' => '.',
                      'thousands_sep' => ',',
                      'timezone' => 'America/Chicago',
                      'template' => 'Default-Blue-Rewrite',
                      'language' => 'en_US',
                      'video_extensions' => 'avi,mpg,mpeg,flv,f4v,rm,asf,wmv,mov,mp4,ts,m2t',
                      'video_size' => '512x384',
                      'video_bitrate' => '26',
                      'audio_bitrate' => '128',
                      'thumb_size' => '120x90',
                      'thumb_quality' => '90',
                      'thumb_amount' => '15',
                      'max_upload_size' => '50MB',
                      'max_upload_duration' => '00:20:00',
                      'flag_mod_rewrite' => '1',
                      'mailer' => 'mail',
                      'flag_user_confirm_email' => '0',
                      'date_format' => 'm-d-Y',
                      'time_format' => 'h:i:s',
                      'avatar_dimensions' => '200x200',
                      'avatar_filesize' => '100KB',
                      'avatar_extensions' => 'jpg,gif,png',
                      'flag_user_strip_tags' => '1',
                      'video_format' => '0',
                      'flag_allow_uploads' => '1',
                      'flag_upload_reject_duplicates' => '1',
                      'flag_upload_allow_private' => '1',
                      'flag_upload_convert' => '',
                      'flag_upload_review' => '',
                      'upload_extensions' => 'avi,mpg,mpeg,flv,f4v,rm,asf,wmv,mov,mp4,ts,m2t',
                      'title_min_length' => '10',
                      'title_max_length' => '100',
                      'description_min_length' => '10',
                      'description_max_length' => '500',
                      'tags_min' => '1',
                      'tags_max' => '10',
                      'flag_video_strip_tags' => '1',
                      'comment_max_length' => '500',
                      'comment_throttle_period' => '120',
                      'flag_comment_strip_tags' => '1',
                      'captcha_min_length' => '4',
                      'captcha_max_length' => '6',
                      'flag_captcha_words' => '1',
                      'flag_captcha_on_signup' => '1',
                      'flag_captcha_on_upload' => '0',
                      'flag_captcha_on_comment' => '1',
                      'cache_main' => '3600',
                      'cache_search' => '3600',
                      'cache_categories' => '3600',
                      'cache_browse' => '3600',
                      'cache_video' => '3600',
                      'cache_profile' => '3600',
                      'cache_comments' => '3600',
                      'cache_custom' => '3600');


    // Reset Config.php file
    Config::Save($settings, true);


    // Reset database.xml file
    $schema = GetDBSchema();
    $custom_tables = array(array('name' => 'tbx_user_custom', 'field' => 'username'),
                           array('name' => 'tbx_video_custom', 'field' => 'video_id'),
                           array('name' => 'tbx_category_custom', 'field' => 'category_id'),
                           array('name' => 'tbx_sponsor_custom', 'field' => 'sponsor_id'));

    foreach( $custom_tables as $table )
    {
        $xtable = $schema->el('//table[name="' . $table['name'] . '"]');

        foreach( $xtable->xpath('./columns/column') as $xcolumn )
        {
            if( $xcolumn->name->val() != $table['field'] )
            {
                XML_Schema::DeleteColumn($table['name'], $xcolumn->name->val());
            }
        }
    }


    // Remove all database tables
    foreach( $tables as $table )
    {
        $DB->Update('DROP TABLE IF EXISTS #', array($table));
    }


    // Clear out directories
    $dirs = array('temp', 'uploads', 'videos', 'templates/_cache');
    foreach( $dirs as $dir )
    {
        $dir = BASE_DIR . '/' . $dir;

        // Remove sub-directories and their contents
        $removals = Dir::ReadDirectories($dir, '~^[^.]~');
        foreach( $removals as $removal )
        {
            Dir::Remove($dir . '/' . $removal);
        }

        // Remove files
        $files = Dir::ReadFiles($dir, '~^[^.]~');
        foreach($files as $file)
        {
            @unlink($dir . '/' . $file);
        }
    }
}

$fp = fopen(__FILE__, 'r');
fseek($fp, __COMPILER_HALT_OFFSET__);
eval(stream_get_contents($fp));
fclose($fp);

__halt_compiler();?>
<html>
<head>
  <title>Reset TubeX Installation</title>
</head>
<body>

<h1>Reset TubeX Installation</h1>

<?php if( !isset($reset) ): ?>
<form action="reset-install.php" method="post">
  Press the button below to reset your TGPX installation to it's default state.<br />
  This will delete all of the data and settings that you have configured up to this point.
  <br /><br />
  <input type="submit" value="Reset Installation" />
</form>
<?php else: ?>
Your TubeX installation has been reset.  Upload the install.php script and access it through your browser to re-initialize the software.
<?php endif; ?>


</body>
</html>