<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#


// General defines
define('BASE_DIR', realpath(dirname(__FILE__) . '/../'));
define('INCLUDES_DIR', BASE_DIR . '/includes');
define('DATA_DIR', BASE_DIR . '/data');
define('TEMPLATE_CACHE_DIR', BASE_DIR . '/templates/_cache');
define('TEMPLATE_COMPILE_DIR', BASE_DIR . '/templates/_compiled');
define('UPLOADS_DIR', BASE_DIR . '/uploads');
define('VIDEOS_DIR', BASE_DIR . '/videos');
define('LANGUAGE_DIR', BASE_DIR . '/language');
define('TEMP_DIR', BASE_DIR . '/temp');
define('CACHE_CATEGORIES', 'cache_categories');
define('CACHE_SPONSORS', 'cache_sponsors');
define('LOGIN_COOKIE', 'tbxlogin');
define('STATS_COOKIE', 'tbxstats');
define('TABLE_PREFIX', 'tbx_');
define('ZIP_EXTENSION', 'zip');
define('JPG_EXTENSION', 'jpg');
define('IMAGE_EXTENSIONS', 'jpg|jpeg|bmp|png|gif|tga|tif|tiff');
define('QT_EXTENSIONS', 'mov|qt|mpg|mpeg');
define('WM_EXTENSIONS', 'wmv');
define('FLASH_EXTENSIONS', 'flv|mp4|f4v');


// Setup error reporting level

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);



// Initialize some PHP settings
set_include_path('.' . PATH_SEPARATOR . BASE_DIR . '/includes' . PATH_SEPARATOR . BASE_DIR . '/classes');
@set_magic_quotes_runtime(0);
@set_time_limit(0);

try
{
    @ini_set('zend.ze1_compatibility_mode', 'Off');
    @ini_set('pcre.backtrack_limit', 1000000);
    @ini_set('memory_limit', -1);
}
catch(Exception $e) { }





class Config
{

    const TAG_NAME = 'SETTINGS';

    private static $settings =
        array(
//<SETTINGS>
              'cookie_path' => '/',
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
              'cache_custom' => '3600',
              'random_value' => '8035c9ca1fef4c4d155ba51d33e6804f1ab35c99'
//</SETTINGS>
             );

    private function __construct() {}

    public static function Get($setting)
    {
        return isset(self::$settings[$setting]) ? self::$settings[$setting] : null;
    }

    public static function GetAll()
    {
        return self::$settings;
    }

    public static function Set($setting, $value)
    {
        self::$settings[$setting] = $value;
    }

    public static function Save($new_settings, $overwrite = false)
    {
        self::$settings = $overwrite ? $new_settings : array_merge(self::$settings, $new_settings);
        self::$settings['random_value'] = sha1(uniqid(rand(), true));

        $elements = array();
        foreach(self::$settings as $setting => $value)
        {
            $elements[] = "'$setting' => '" . addslashes($value) . "'";
        }

        $config = preg_replace('~//<'.self::TAG_NAME.'>.*?//</'.self::TAG_NAME.'>~msi',
                               "//<".self::TAG_NAME.">\n              " . join(",\n              ", $elements) . "\n//</".self::TAG_NAME.">",
                               str_replace(array("\r\n", "\r"), "\n", file_get_contents(BASE_DIR . '/classes/Config.php')));

        file_put_contents(BASE_DIR . '/classes/Config.php', $config);
    }

    public static function Defaults()
    {
        // Set the default timezone
        date_default_timezone_set(self::$settings['timezone']);

        // Define video extensions
        define('VIDEO_EXTENSIONS', str_replace(array('.',','), array('', '|'), self::$settings['video_extensions']));
        define('TEMPLATES_DIR', BASE_DIR . '/templates/' . self::$settings['template']); // Patched TEMPLATES_DIR

    }
}



// Load configuration defaults
Config::Defaults();

function GetDB()
{

    static $instance;

    if( !isset($instance) )
    {
        $instance = new Database_MySQL(Config::Get('db_username'), Config::Get('db_password'), Config::Get('db_database'), Config::Get('db_hostname'));
    }

    return $instance;
}

function _T()
{
    static $ini = null;

    if( empty($ini) )
    {
        $language = Config::Get('language');

        if( version_compare(PHP_VERSION, '5.3.0', '>=') && version_compare(PHP_VERSION, '5.3.2', '<') )
        {
            $ini = array();
            $contents = file_get_contents(LANGUAGE_DIR . "/$language.ini");
            $contents = String::FormatNewlines($contents, String::NEWLINE_UNIX);

            foreach( explode(String::NEWLINE_UNIX, $contents) as $line )
            {
                if( preg_match('~^\[(.*?)\]$~', $line, $matches) )
                {
                    $sect = $matches[1];
                    $ini[$sect] = array();
                }
                else if( preg_match('~^(.*?)=[\'"]?([^\'"]+)[\'"]?~', $line, $matches) )
                {
                    $ini[$sect][$matches[1]] = html_entity_decode($matches[2]);
                }
            }
        }
        else
        {
            $ini = parse_ini_file(LANGUAGE_DIR . "/$language.ini", true);
        }
    }

    $args = func_get_args();
    list($section, $term) = explode(':', $args[0], 2);
    if( isset($ini[$section][$term]) )
    {
        $args[0] = html_entity_decode($ini[$section][$term]);
        return call_user_func_array('sprintf', $args);
    }
    else
    {
        return $args[0];
    }
}

function NumberFormatInteger($integer)
{
    return number_format($integer, 0, Config::Get('dec_point'), Config::Get('thousands_sep'));
}

function StatsRollover($cron = false)
{
    if( Config::Get('flag_using_cron') && !$cron )
    {
        return;
    }

    $times = array();
    $file = DATA_DIR . '/times';
    $ufile = INCLUDES_DIR . '/rollover';
    if( file_exists($file) )
    {
        if( !is_writeable($file) )
        {
            file_put_contents(DATA_DIR . '/error_log', "[" . date('m-d-Y h:i:sa') . "] " . $file . " is not writeable\n", FILE_APPEND);
            return;
        }

        $fp = fopen($file, 'r+');

        // One at a time please
        flock($fp, LOCK_EX | LOCK_NB, $wouldblock);
        if( $wouldblock )
        {
            fclose($fp);
            return;
        }

        $times = unserialize(fgets($fp));
        $this_month = date('Ym');
        $this_week = date('Ymd', date('w') == 0 ? strtotime('this sunday') : strtotime('last sunday'));
        $this_day = date('Ymd');

        $updates = null;
        if( $this_month != $times['last_month'] )
        {
            $updates = array_slice(file($ufile), 0, 3);

            $times['last_day'] = $this_day;
            $times['last_week'] = $this_week;
            $times['last_month'] = $this_month;
        }
        else if( $this_week != $times['last_week'] )
        {
            $updates = array_slice(file($ufile), 3, 3);

            $times['last_day'] = $this_day;
            $times['last_week'] = $this_week;
        }
        else if( $this_day != $times['last_day'] )
        {
            $updates = array_slice(file($ufile), 6, 3);

            $times['last_day'] = $this_day;
        }

        if( !empty($updates) )
        {
            $DB = GetDB();

            foreach( $updates as $update )
            {
                $DB->Update($update);
            }

            fseek($fp, 0, SEEK_SET);
            fwrite($fp, serialize($times));
            fflush($fp);
            ftruncate($fp, ftell($fp));
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($file, 0666);
    }
    else
    {
        $times['last_day'] = date('Ymd');
        $times['last_week'] = date('Ymd', date('w') == 0 ? strtotime('this sunday') : strtotime('last sunday'));
        $times['last_month'] = date('Ym');

        file_put_contents($file, serialize($times));
        @chmod($file, 0666);
    }
}

if( function_exists('spl_autoload_register') )
{
    function JMBAutoload($class)
    {
        $filename = str_replace('_', '/', $class) . '.php';
        require_once($filename);
    }

    spl_autoload_register('JMBAutoload');
}
else
{
    function __autoload($class)
    {
        $filename = str_replace('_', '/', $class) . '.php';
        require_once($filename);
    }
}

?>