<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

define('SERVER_INFO_TEST', 'SERVER_INFO_TEST');

if( isset($_SERVER[SERVER_INFO_TEST]) )
{
    $si = ServerInfo::Get();
    echo serialize($si);
    exit;
}



class ServerInfo
{
    // The name to use for caching this information
    const CACHE_NAME = 'ServerInfo';

    // How long to cache this information, in seconds
    const CACHE_LIFETIME = 86400;

    // Minimum PHP version required
    const MIN_PHP_VERSION = '5.2.0';

    // Minimum MySQL version required
    const MIN_MYSQL_VERSION = '4.1.0';

    // PHP Extensions
    const EXT_CURL = 'curl';
    const EXT_DOM = 'dom';
    const EXT_GD = 'gd';
    const EXT_JSON = 'json';
    const EXT_MYSQL = 'mysql';
    const EXT_PCRE = 'pcre';
    const EXT_SIMPLEXML = 'simplexml';
    const EXT_ZIP = 'zip';

    // PHP Settings
    const PHP_DISABLE_FUNCTIONS = 'disable_functions';
    const PHP_FILE_UPLOADS = 'file_uploads';
    const PHP_MEMORY_LIMIT = 'memory_limit';
    const PHP_OPEN_BASEDIR = 'open_basedir';
    const PHP_POST_MAX_SIZE = 'post_max_size';
    const PHP_SAFE_MODE = 'safe_mode';
    const PHP_UPLOAD_MAX_FILESIZE = 'upload_max_filesize';

    // BINARIES
    const BIN_NICE = 'nice';
    const BIN_MENCODER = 'mencoder';
    const BIN_MPLAYER = 'mplayer';
    const BIN_FFMPEG = 'ffmpeg';
    const BIN_CONVERT = 'convert';
    const BIN_DIG = 'dig';
    const BIN_MYSQL = 'mysql';
    const BIN_MYSQLDUMP = 'mysqldump';
    const BIN_YAMDI = 'yamdi';
    const BIN_PHP = 'php';
    const BIN_PHP_CLI = 'php-cli';
    const BIN_PHP_CGI = 'php-cgi';
    const BIN_PS = 'ps';
    const BIN_MP4BOX = 'MP4Box';
    const BIN_MP4BOX_FREEBSD = 'mp4box';

    private static $instance = null;

    private static $binary_dirs = array('/bin',
                                        '/usr/bin',
                                        '/usr/local/bin',
                                        '/usr/local/mysql/bin',
                                        '/sbin',
                                        '/usr/sbin',
                                        '/usr/lib',
                                        '/usr/local/ImageMagick/bin',
                                        '/usr/X11R6/bin');

    public $errors = array();

    private $error_encountered = false;

    private $error_encountered_clearable = false;

    private $generated;

    public $os_name;

    public $os_version;

    public $os_valid = false;

    public $php_version;

    public $php_sapi;

    public $php_version_valid = false;

    public $php_extensions = array();

    public $php_settings = array();

    public $php_restricted_mode = false;

    public $php_cli_serverinfo;

    public $php_cgi_serverinfo;

    public $php_version_buggy = false;

    public $ini_get_disabled = false;

    public $shell_exec_disabled = false;

    public $proc_open_disabled = false;

    public $pcre_unicode = false;

    public $mysql_version;

    public $mysql_version_valid = false;

    public $binaries = array();

    public $php_binary_is_cli = true;

    public $mencoder_old = false;
    public $mplayer_old = false;

    public $mencoder_codecs;

    public $mencoder_x264 = false;
    public $mencoder_lavc = false;
    public $mencoder_lavf = false;
    public $mencoder_faac = false;
    public $mencoder_vfw = false;
    public $mencoder_mp3lame = false;

    public $can_convert = false;
    public $can_thumbnail = false;
    public $can_flv = false;
    public $can_vp6 = false;
    public $can_mp4 = false;

    private function __construct()
    {
        // Set temporary error handler
        set_error_handler(array($this, 'ErrorHandler'));


        // Time this was generated
        $this->generated = time();


        // Get OS information
        $this->os_name = php_uname('s');
        $this->os_version = php_uname('r');
        $this->os_valid = strpos(strtolower($this->os_name), 'windows') === false;



        // Get base PHP information
        $this->php_sapi = PHP_SAPI;
        $this->php_version = PHP_VERSION;
        list($a, $b, $c) = explode('.', $this->php_version);
        list($x, $y, $z) = explode('.', self::MIN_PHP_VERSION);
        $this->php_version_valid = ($a > $x || ($a == $x && $b > $y) || ($a == $x && $b == $y && $c >= $z));

        if( version_compare($this->php_version, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.2.2', '<=') )
        {
            $this->php_version_buggy = true;
        }


        // Get PHP extension information
        $this->php_extensions[self::EXT_CURL] = extension_loaded(self::EXT_CURL);
        $this->php_extensions[self::EXT_DOM] = extension_loaded(self::EXT_DOM);
        $this->php_extensions[self::EXT_GD] = extension_loaded(self::EXT_GD);
        $this->php_extensions[self::EXT_JSON] = extension_loaded(self::EXT_JSON);
        $this->php_extensions[self::EXT_MYSQL] = extension_loaded(self::EXT_MYSQL);
        $this->php_extensions[self::EXT_PCRE] = extension_loaded(self::EXT_PCRE);
        $this->php_extensions[self::EXT_SIMPLEXML] = extension_loaded(self::EXT_SIMPLEXML);
        $this->php_extensions[self::EXT_ZIP] = extension_loaded(self::EXT_ZIP);


        // Check if PCRE has unicode support
        if( $this->php_extensions[self::EXT_PCRE] )
        {
            $this->pcre_unicode = @preg_match('/\pL/u', 'a') == 1;
        }


        // Get MySQL information
        if( $this->php_extensions[self::EXT_MYSQL] )
        {
            $this->mysql_version = mysql_get_client_info();
            list($a, $b, $c) = explode('.', $this->mysql_version);
            list($x, $y, $z) = explode('.', self::MIN_MYSQL_VERSION);
            $this->mysql_version_valid = ($a > $x || ($a == $x && $b > $y) || ($a == $x && $b == $y && $c >= $z));
        }


        // Get PHP settings (test if ini_get is disabled first)
        $this->error_encountered_clearable = false;
        ini_get(self::PHP_SAFE_MODE);
        $this->ini_get_disabled = $this->error_encountered_clearable;
        if( !$this->ini_get_disabled )
        {
            $this->php_settings[self::PHP_DISABLE_FUNCTIONS] = self::IniGet(self::PHP_DISABLE_FUNCTIONS);
            $this->php_settings[self::PHP_FILE_UPLOADS] = self::IniGet(self::PHP_FILE_UPLOADS);
            $this->php_settings[self::PHP_MEMORY_LIMIT] = self::IniGet(self::PHP_MEMORY_LIMIT);
            $this->php_settings[self::PHP_OPEN_BASEDIR] = self::IniGet(self::PHP_OPEN_BASEDIR);
            $this->php_settings[self::PHP_POST_MAX_SIZE] = self::IniGet(self::PHP_POST_MAX_SIZE);
            $this->php_settings[self::PHP_SAFE_MODE] = self::IniGet(self::PHP_SAFE_MODE);
            $this->php_settings[self::PHP_UPLOAD_MAX_FILESIZE] = self::IniGet(self::PHP_UPLOAD_MAX_FILESIZE);
        }
        else
        {
            $this->php_restricted_mode = true;
        }


        // Try shell_exec
        $this->error_encountered_clearable = false;
        shell_exec('ls -l');
        $this->shell_exec_disabled = $this->error_encountered_clearable;


        // Try proc_open
        $this->error_encountered_clearable = false;
        $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
        $process = proc_open('ls -l', $descriptorspec, $pipes);
        proc_close($process);
        $this->proc_open_disabled = $this->error_encountered_clearable;


        if( $this->shell_exec_disabled )
        {
            $this->binaries[self::BIN_CONVERT] = null;
            $this->binaries[self::BIN_DIG] = null;
            $this->binaries[self::BIN_FFMPEG] = null;
            $this->binaries[self::BIN_MENCODER] = null;
            $this->binaries[self::BIN_MP4BOX] = null;
            $this->binaries[self::BIN_MPLAYER] = null;
            $this->binaries[self::BIN_MYSQL] = null;
            $this->binaries[self::BIN_MYSQLDUMP] = null;
            $this->binaries[self::BIN_NICE] = null;
            $this->binaries[self::BIN_PHP_CLI] = null;
            $this->binaries[self::BIN_PHP_CGI] = null;
            $this->binaries[self::BIN_PS] = null;
            $this->binaries[self::BIN_YAMDI] = null;
        }
        else if( !isset($_SERVER[SERVER_INFO_TEST]) )
        {
            // Setup additional binary search directories
            if( defined('BASE_DIR') )
            {
                self::$binary_dirs[] = BASE_DIR . '/bin';
            }

            if( isset($_SERVER['DOCUMENT_ROOT'])  )
            {
                self::$binary_dirs[] = realpath($_SERVER['DOCUMENT_ROOT'] . '/../bin');
            }

            // Locate binaries
            $this->binaries[self::BIN_CONVERT] = self::LocateBinary(self::BIN_CONVERT, $this->php_settings[self::PHP_OPEN_BASEDIR], '-version', 'ImageMagick 6\.[2-9]');

            if( !empty($this->binaries[self::BIN_CONVERT]) )
            {
                $output = shell_exec($this->binaries[self::BIN_CONVERT] . ' -list configure');

                if( stripos($output, 'ljpeg') === false )
                {
                    $this->binaries[self::BIN_CONVERT] = null;
                }
            }

            $this->binaries[self::BIN_DIG] = self::LocateBinary(self::BIN_DIG, $this->php_settings[self::PHP_OPEN_BASEDIR], '-v', 'DiG');
            $this->binaries[self::BIN_FFMPEG] = self::LocateBinary(self::BIN_FFMPEG, $this->php_settings[self::PHP_OPEN_BASEDIR], '-version', 'FFmpeg version');
            $this->binaries[self::BIN_MENCODER] = self::LocateBinary(self::BIN_MENCODER, $this->php_settings[self::PHP_OPEN_BASEDIR], '-ovc help', 'MPlayer Team');
            $this->binaries[self::BIN_MPLAYER] = self::LocateBinary(self::BIN_MPLAYER, $this->php_settings[self::PHP_OPEN_BASEDIR], '-h', 'Usage:\s+mplayer\s+\[options\]');
            $this->binaries[self::BIN_MYSQL] = self::LocateBinary(self::BIN_MYSQL, $this->php_settings[self::PHP_OPEN_BASEDIR], '-V', 'mysql');
            $this->binaries[self::BIN_MYSQLDUMP] = self::LocateBinary(self::BIN_MYSQLDUMP, $this->php_settings[self::PHP_OPEN_BASEDIR], '-V', 'mysqldump');
            $this->binaries[self::BIN_NICE] = self::LocateBinary(self::BIN_NICE, $this->php_settings[self::PHP_OPEN_BASEDIR], '--version', 'coreutils');
            $this->binaries[self::BIN_YAMDI] = self::LocateBinary(self::BIN_YAMDI, $this->php_settings[self::PHP_OPEN_BASEDIR], '-h', 'metadata injector');
            $this->binaries[self::BIN_MP4BOX] = self::LocateBinary(self::BIN_MP4BOX, $this->php_settings[self::PHP_OPEN_BASEDIR], '-version', 'GPAC');
            $this->binaries[self::BIN_PS] = self::LocateBinary(self::BIN_PS, $this->php_settings[self::PHP_OPEN_BASEDIR], '-V', 'procps');
            $this->binaries[self::BIN_PHP_CLI] = self::LocateBinary(self::BIN_PHP, $this->php_settings[self::PHP_OPEN_BASEDIR], '-v', '\(cli\)', 'SCRIPT_FILENAME=/dev/null');

            // FreeBSD ports incorrectly names the executable mp4box
            if( empty($this->binaries[self::BIN_MP4BOX]) )
            {
                 $this->binaries[self::BIN_MP4BOX] = self::LocateBinary(self::BIN_MP4BOX_FREEBSD, $this->php_settings[self::PHP_OPEN_BASEDIR], '-version', 'GPAC');
            }

            if( empty($this->binaries[self::BIN_PHP_CLI]) )
            {
                $this->binaries[self::BIN_PHP_CLI] = self::LocateBinary(self::BIN_PHP_CLI, $this->php_settings[self::PHP_OPEN_BASEDIR], '-v', '\(cli\)', 'SCRIPT_FILENAME=/dev/null');
            }

            // Get information about CLI version of PHP
            if( !empty($this->binaries[self::BIN_PHP_CLI]) && !isset($_SERVER[SERVER_INFO_TEST]) )
            {
                $this->php_cli_serverinfo = unserialize(shell_exec(SERVER_INFO_TEST . '=true ' . $this->binaries[self::BIN_PHP_CLI] . ' ' . __FILE__));
            }


            // Locate CGI version of PHP if CLI is not available
            if( empty($this->binaries[self::BIN_PHP_CLI]) )
            {
                $this->php_binary_is_cli = false;

                $this->binaries[self::BIN_PHP_CGI] = self::LocateBinary(self::BIN_PHP, $this->php_settings[self::PHP_OPEN_BASEDIR], '-v', '\(cgi\)', 'SCRIPT_FILENAME=/dev/null');

                if( empty($this->binaries[self::BIN_PHP_CGI]) )
                {
                    $this->binaries[self::BIN_PHP_CGI] = self::LocateBinary(self::BIN_PHP_CGI, $this->php_settings[self::PHP_OPEN_BASEDIR], '-v', '\(cgi\)', 'SCRIPT_FILENAME=/dev/null');
                }

                // Get information about CGI version of PHP
                if( !empty($this->binaries[self::BIN_PHP_CGI]) && !isset($_SERVER[SERVER_INFO_TEST]) )
                {
                    $this->php_cgi_serverinfo = unserialize(shell_exec(SERVER_INFO_TEST . '=true SCRIPT_FILENAME=' . __FILE__ . ' '  . $this->binaries[self::BIN_PHP_CGI] . ' -q'));
                }
            }

            $this->binaries[self::BIN_PHP] = empty($this->binaries[self::BIN_PHP_CLI]) ? $this->binaries[self::BIN_PHP_CGI] : $this->binaries[self::BIN_PHP_CLI];


            // Check for old version of MPlayer
            if( !empty($this->binaries[self::BIN_MPLAYER]) )
            {
                $output = shell_exec($this->binaries[self::BIN_MPLAYER] . " -h 2>&1");

                if( preg_match('~MPlayer 1\.0rc~i', $output) )
                {
                    $this->binaries[self::BIN_MPLAYER] = null;
                    $this->mplayer_old = true;
                }
            }

            // Check for old version of MEncoder
            if( !empty($this->binaries[self::BIN_MENCODER]) )
            {
                $output = shell_exec($this->binaries[self::BIN_MENCODER] . " -ovc help 2>&1");

                if( preg_match('~MEncoder 1\.0rc~i', $output) )
                {
                    $this->binaries[self::BIN_MENCODER] = null;
                    $this->mencoder_old = true;
                }
            }

            // Get mencoder codecs
            if( !empty($this->binaries[self::BIN_MENCODER]) )
            {
                $this->mencoder_codecs = preg_replace('~\n+~s', "\n", shell_exec($this->binaries[self::BIN_MENCODER] . ' -ovc help -oac help -of help 2>&1'));
                $this->mencoder_codecs = preg_replace(array('~.*Available codecs:~Usi',
                                                            '~Available codecs:~',
                                                            '~Available codecs:~'),
                                                      array('Available codecs:',
                                                            'Video Codecs:',
                                                            'Audio Codecs:'),
                                                      $this->mencoder_codecs, 1);

                $this->mencoder_faac = stripos($this->mencoder_codecs, 'faac') !== false;
                $this->mencoder_x264 = stripos($this->mencoder_codecs, 'x264') !== false;
                $this->mencoder_lavc = stripos($this->mencoder_codecs, 'lavc') !== false;
                $this->mencoder_lavf = stripos($this->mencoder_codecs, 'lavf') !== false;
                $this->mencoder_vfw = stripos($this->mencoder_codecs, 'vfw') !== false;
                $this->mencoder_mp3lame = stripos($this->mencoder_codecs, 'mp3lame') !== false;
            }
        }

        $this->can_flv = $this->mencoder_lavc && $this->mencoder_lavf && $this->mencoder_mp3lame && $this->binaries[self::BIN_YAMDI];
        $this->can_mp4 = $this->mencoder_x264 && $this->mencoder_faac && $this->binaries[self::BIN_MP4BOX];
        $this->can_thumbnail = !$this->shell_exec_disabled && $this->binaries[self::BIN_PHP] && $this->binaries[self::BIN_MPLAYER];
        $this->can_vp6 = $this->mencoder_vfw && $this->mencoder_lavf && $this->mencoder_mp3lame && $this->binaries[self::BIN_YAMDI];

        $this->can_convert = !$this->shell_exec_disabled &&
                             !$this->proc_open_disabled &&
                             $this->binaries[self::BIN_PHP] &&
                             $this->binaries[self::BIN_MENCODER] &&
                             ($this->can_flv || $this->can_mp4 || $this->can_vp6);

        restore_error_handler();
    }

    private function ErrorHandler($code, $string, $file, $line)
    {
        switch($code)
        {
            case E_WARNING:
            case E_ERROR:
            case E_RECOVERABLE_ERROR:
                $this->errors[] = strip_tags($string) . " in " . basename($file) . " on line $line";
                $this->error_encountered = true;
                $this->error_encountered_clearable = true;
                break;
        }
    }

    private static function LocateBinary($binary, $open_basedir = false, $output_arg = null, $output_search = null, $prefix = null)
    {
        // No open_basedir restriction
        if( !$open_basedir )
        {
            foreach( self::$binary_dirs as $dir )
            {
                if( @is_file("$dir/$binary") && @is_executable("$dir/$binary") )
                {
                    if( $output_arg )
                    {
                        $output = shell_exec("$prefix $dir/$binary $output_arg 2>&1");

                        if( preg_match('~' . $output_search . '~i', $output) )
                        {
                            return "$dir/$binary";
                        }
                    }
                    else
                    {
                        return "$dir/$binary";
                    }
                }
            }
        }

        $which = trim(shell_exec("which $binary 2>&1"));

        if( !empty($which) && !preg_match("~no $banary~", $which) )
        {
            if( $output_arg )
            {
                $output = shell_exec("$prefix $which $output_arg 2>&1");

                if( preg_match('~' . $output_search . '~i', $output) )
                {
                    return $which;
                }
            }
            else
            {
                return $which;
            }
        }


        $whereis = trim(shell_exec('whereis -B ' . join(' ', self::$binary_dirs)." -f $binary 2>&1"));
        preg_match("~$binary: (.*)~", $whereis, $matches);
        $whereis = explode(' ', trim($matches[1]));

        if( count($whereis) )
        {
            if( $output_arg )
            {
                foreach( $whereis as $executable )
                {
                    $output = shell_exec("$prefix $executable $output_arg 2>&1");

                    if( preg_match('~' . $output_search . '~i', $output) )
                    {
                        return $executable;
                    }
                }
            }
            else
            {
                return $whereis[0];
            }
        }

        return null;
    }

    private static function IniGet($varname)
    {
        $value = ini_get($varname);

        switch(strtolower($value))
        {
            case '0':
            case '':
            case 'off':
                $value = false;
                break;

            case '1':
            case 'on':
                $value = true;
                break;
        }

        return $value;
    }

    public static function Get($cache = false)
    {
        if( !is_object(self::$instance) )
        {
            self::$instance = new ServerInfo();

            if( $cache )
            {
                self::Cache();
            }
        }

        return self::$instance;
    }

    public static function Cache()
    {
        Cache_MySQL::Cache(self::CACHE_NAME, serialize(self::$instance));
    }

    public static function GetCached()
    {
        if( is_object(self::$instance) )
        {
            return self::$instance;
        }

        $cached = Cache_MySQL::Get(self::CACHE_NAME);

        if( empty($cached) )
        {
            return self::Get(true);
        }

        $si = unserialize($cached);

        if( time() - $si->generated >= self::CACHE_LIFETIME )
        {
            return self::Get(true);
        }

        self::$instance = $si;
        return self::$instance;
    }

    public function __toString()
    {
        $string = "Operating System\n" .
                  "================\n" .
                  'Name/Version: ' . $this->os_name . ' ' . $this->os_version . "\n" .
                  'Compatible: ' . self::BoolToString($this->os_valid) . "\n" .
                  "\nPHP\n" .
                  "===\n" .
                  'Version: ' . $this->php_version . "\n" .
                  'SAPI: ' . $this->php_sapi . "\n" .
                  'Compatible: ' . self::BoolToString($this->php_version_valid) . "\n" .
                  "\nMySQL\n" .
                  "=====\n" .
                  'Version: ' . $this->mysql_version . "\n" .
                  'Compatible: ' . self::BoolToString($this->mysql_version_valid) . "\n" .
                  "\nPHP Extensions\n" .
                  "==============\n";

        foreach( $this->php_extensions as $extension => $loaded )
        {
            $string .= "$extension: " . self::BoolToString($loaded) . "\n";
        }

        $string .= "\nphp.ini Settings\n" .
                   "================\n";

        foreach( $this->php_settings as $setting => $value )
        {
            $string .= "$setting: " . self::BoolToString($value) . "\n";
        }

        $string .= "\nBinary Locations\n" .
                   "================\n";

        foreach( $this->binaries as $binary => $path )
        {
            $string .= "$binary: $path\n";
        }


        if( !empty($this->binaries[self::BIN_MENCODER]) )
        {
            $string .= "\nMEncoder Codecs\n" .
                       "===============\n" .
                       $this->mencoder_codecs . "\n";
        }

        $string .= "\nErrors Encountered\n" .
                   "==================\n";

        foreach( $this->errors as $error )
        {
            $string .= $error . "\n";
        }

        if( !empty($this->php_cli_serverinfo) )
        {
            $string .= "\n\n*********************************** CLI VERSION OF PHP ***********************************\n" .
                       $this->php_cli_serverinfo;
        }

        if( !empty($this->php_cgi_serverinfo) )
        {
            $string .= "\n\n*********************************** CGI VERSION OF PHP ***********************************\n" .
                       $this->php_cli_serverinfo;
        }

        return $string;
    }

    private static function BoolToString($boolean)
    {
        if( is_bool($boolean) )
        {
            return $boolean ? 'true' : 'false';
        }
        else
        {
            return $boolean;
        }
    }
}

?>
