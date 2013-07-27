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

function t_formfield($field)
{
    switch($field['type'])
    {
        case Form_Field::CHECKBOX:
            return '<input type="checkbox" ' .
                   'value="1" ' .
                   'name="' . $field['name'] . '" ' .
                   ($_REQUEST[$field['name']] ? ' checked="checked"' : '') .
                   $field['tag_attributes'] . ' />';

        case Form_Field::SELECT:
            return '<select name="' . $field['name'] . '" ' . $field['tag_attributes'] . '>' .
                   Form_Field::OptionsSimple($field['options'], $_REQUEST[$field['name']]) .
                   '</select>';

        case Form_Field::TEXT:
            return '<input type="text" ' .
                   'name="' . $field['name'] . '" ' .
                   'value="' . htmlspecialchars($_REQUEST[$field['name']], ENT_QUOTES) . '" ' .
                   $field['tag_attributes'] . ' />';

        case Form_Field::TEXTAREA:
            return '<textarea name="' . $field['name'] . '" ' . $field['tag_attributes'] . '>' . htmlspecialchars($_REQUEST[$field['name']]) . '</textarea>';
    }
}

function t_translate($string, $prefix)
{
    return _T("$prefix:$string");
}

function t_age($date)
{
    $timediff = strtotime(date('Y-m-d') . ' 23:59:59') - strtotime($date);

    // Older than 1 year
    if( $timediff >= 31556926 )
    {
        $years = floor($timediff / 31556926);
        return $years > 1 ? _T('Text:years ago', $years) : _T('Text:year ago', $years);
    }

    // Older than 5 weeks
    else if( $timediff >= 2629744 )
    {
        $months = floor($timediff / 2629744);
        return $months > 1 ? _T('Text:months ago', $months) : _T('Text:month ago', $months);
    }

    // Older than 6 days
    else if( $timediff >= 604800 )
    {
        $weeks = floor($timediff / 604800);
        return $weeks > 1 ? _T('Text:weeks ago', $weeks) : _T('Text:week ago', $weeks);
    }

    // Older than today
    else if( $timediff >= 86400 )
    {
        $days = floor($timediff / 86400);
        return $days > 1 ? _T('Text:days ago', $days) : _T('Text:day ago', $days);
    }

    else
    {
        return _T('Today');
    }
}

function t_duration($seconds)
{
    return gmdate($seconds < 3600 ? 'i:s' : 'H:i:s', $seconds);
}

function t_tostring($string)
{
    if( preg_match('~^\d+$~', $string) )
    {
        return number_format($string, 0, Config::Get('dec_point'), Config::Get('thousands_sep'));
    }

    return $string;
}

function t_chop_words($string, $length = 100, $append = '...')
{
    if( strlen($string) <= $length )
    {
        return $string;
    }

    $len_append = strlen($append);
    $chopped = substr($string, 0, $length - $len_append);

    return ($string[$length - $len_append] == ' ' ?
           $chopped :
           substr($chopped, 0, strrpos($chopped, ' ')))  . $append;
}

function t_chop($string, $length = 100, $append = '...')
{
    $len_append = strlen($append);
    return strlen($string) > $length ?
           trim(substr($string, 0, $length - $len_append)) . $append :
           $string;
}

function t_nearesthalf($string)
{
    return round($string * 2) / 2;
}

function t_urlify($string, $max_words = null)
{
    if( !empty($max_words) )
    {
        $string = join(' ', array_slice(explode(' ', $string), 0, $max_words));
    }

    return strtolower(preg_replace(array('~[^a-z0-9_-]~i', '~-+~', '~-+$~'), array('-', '-', ''), $string));
}

function t_datetime($string, $format = 'M j, Y g:ia')
{
    return date($format, strtotime($string));
}

function t_date($string, $format = 'M j, Y')
{
    return date($format, strtotime($string . ' 12:00:00'));
}

function t_singlequotes($string)
{
    return str_replace("'", "", $string);
}

class Template
{
    public $caching;
    public $cache_lifetime;
    public $force_compile = false;

    private $template_dir;
    private $compile_dir;
    private $cache_dir;
    private $nocache = false;
    private $vars = array();
    private $captures = array();

    public function __construct($caching = false, $cache_lifetime = 3600, $force_compile = false)
    {
        $this->caching = $caching;
        $this->cache_lifetime = $cache_lifetime;
        $this->force_compile = $force_compile;
        $this->template_dir = TEMPLATES_DIR;
        $this->compile_dir = TEMPLATE_COMPILE_DIR;
        $this->cache_dir = TEMPLATE_CACHE_DIR;
    }

    public function Assign($variable, $value = null)
    {
        if( !empty($variable) )
        {
            $this->vars[$variable] = $value;
        }
    }

    public function AssignByRef($variable, &$value)
    {
        if( !empty($variable) )
        {
            $this->vars[$variable] = &$value;
        }
    }

    public function ClearCache($template = null, $cache_id = '')
    {
        if( !empty($template) )
        {
            File::Delete($this->GetCacheFilename($template, $cache_id));
        }
        else
        {
            $files = glob($this->cache_dir . '/*.*');
            if( $files !== false )
            {
                foreach( $files as $filename )
                {
                    unlink($filename);
                }
            }
        }
    }

    public function IsCached($template, $cache_id = '', $display = false)
    {
        $cache_file = $this->GetCacheFilename($template, $cache_id);

        if( $this->caching && file_exists($cache_file) && filemtime($cache_file) >= (time() - $this->cache_lifetime) )
        {
            if( $display )
            {
                include_once($cache_file);
            }

            return true;
        }

        return false;
    }

    private function Cache($template, $cache_id, $contents)
    {
        $cache_file = $this->GetCacheFilename($template, $cache_id);
        $this->CreateCacheFile($cache_file);
        File::Overwrite($cache_file, $contents);
    }

    public function ClearCompiled($template = null)
    {
        if( !empty($template) )
        {
            File::Delete($this->compile_dir . '/' . $template);
        }
        else
        {
            foreach( glob($this->compile_dir . '/*.*') as $filename )
            {
                File::Delete($filename);
            }
        }
    }

    public function Display($template, $cache_id = '')
    {
        // Display cached version if available
        if( $this->IsCached($template, $cache_id, true) )
        {
            return;
        }

        // Compile if necessary
        if( !$this->IsCompiled($template) )
        {
            $this->CompileTemplate($template);
        }


        try
        {
            ob_start();
            include($this->compile_dir . "/$template");
            $generated = ob_get_clean();
            $output = $this->nocache ? eval('?>' . $generated) : $generated;
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            $log_message = strip_tags("[" . date('m-d-Y h:i:sa') . "] " . $message . (strtolower(get_class($e)) == 'baseexception' ? $e->getExtras() : ''). "\n" .
                           "\tStack Trace:\n" . preg_replace('~^~m', "\t\t", $e->getTraceAsString())) . "\n\n";

            $fp = fopen(BASE_DIR . '/data/error_log', 'a+');
            fwrite($fp, $log_message);
            fclose($fp);

            echo "An error occurred while processing this page.  Please check the software error log for details";
            return;
        }

        // Write cache file
        if( $this->caching )
        {
            $this->Cache($template, $cache_id, $generated);
        }

        echo $output;
    }

    public function Parse($template)
    {
        $compiled_code = Template_Compiler::Compile($template);
        ob_start();
        eval('?>' . $compiled_code);
        $generated = ob_get_clean();

        return $this->nocache ? eval('?>' . $generated) : $generated;
    }

    public function IsCompiled($template)
    {
        if( $this->force_compile )
        {
            return false;
        }

        $compiled = $this->compile_dir . "/$template";
        $template = $this->template_dir . "/$template";

        if( !file_exists($compiled) || filemtime($template) > filemtime($compiled) )
        {
            return false;
        }

        return true;
    }

    public function CompileTemplate($template)
    {
        $compiled_code = Template_Compiler::CompileFile($template);
        File::Overwrite($this->compile_dir . "/$template", $compiled_code);
    }

    public function SetForceCompile($force = true)
    {
        $this->force_compile = $force;
    }

    private function GetCacheFilename($template, $cache_id = '')
    {
        $prefix = sha1($template . $cache_id);
        $filename = "$prefix#$template";

        return $this->cache_dir . '/' . $prefix[0] . '/' . $prefix[1] . '/' . $filename;
    }

    private function CreateCacheFile($cache_file)
    {
        $filename = basename($cache_file);
        $directory = dirname($cache_file);

        if( !file_exists($directory) )
        {
            $old = umask(0);
            mkdir($directory, 0777, true);
            umask($old);
        }

        File::Overwrite($cache_file, '');
    }
}

