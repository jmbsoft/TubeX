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

require_once('../includes/global.php');

set_include_path(get_include_path() . PATH_SEPARATOR . BASE_DIR . '/admin/includes' . PATH_SEPARATOR . BASE_DIR . '/admin/classes');

// Control panel defines
define('MANY_RESULTS', 500);
define('SAVED_SEARCH_DEFAULT', 'Default');


$t = new Template();

function URLify($string, $max_words = null)
{
    if( !empty($max_words) )
    {
        $string = join(' ', array_slice(explode(' ', $string), 0, $max_words));
    }

    return strtolower(preg_replace(array('~[^a-z0-9_-]~i', '~-+~', '~-+$~'), array('-', '-', ''), $string));
}

function TemplateRecompileAll($directory = TEMPLATES_DIR)
{
    $files = Dir::ReadFiles($directory, '~^(?!email).*?(\.tpl$|\.css$)~');

    foreach( $files as $file )
    {
        $compiled = TEMPLATE_COMPILE_DIR . '/' . $file;

        if( ($code = Template_Compiler::CompileFile($file, $directory)) === false )
        {
            return array('message' => 'Template ' . $file . ' contains errors', 'errors' => Template_Compiler::GetErrors());
        }
        else
        {
            file_put_contents($compiled, $code);
            @chmod($compiled, 0666);
        }
    }

    return true;
}

function FormatXml($xml)
{
    $xml = String::FormatNewlines($xml, String::NEWLINE_UNIX);
    $xml = preg_replace(array('~^\s+~m',
                              '~>\s*<~'),
                        array('',
                              ">\n<"),
                        $xml);

    $indent = 0;
    $indent_by = 2;
    $output = '';
    foreach( explode(String::NEWLINE_UNIX, $xml) as $line )
    {
        if( preg_match('~^(<[^/][^>]+[^/]>)$~U', $line) )
        {
            $output .= str_repeat(' ', $indent++ * $indent_by) . $line . String::NEWLINE_UNIX;
        }
        else if( preg_match('~^(</[^>]+>)$~U', $line) )
        {
            $output .= str_repeat(' ', --$indent * $indent_by) . $line . String::NEWLINE_UNIX;
        }
        else
        {
            $output .= str_repeat(' ', $indent * $indent_by) . $line . String::NEWLINE_UNIX;
        }
    }

    return $output;
}

function ResizeableColumn($label, $value, $numeric = false, $joiner = null, $type = null, $link = null)
{
    if( is_array($value) )
    {
        if( $numeric )
        {
            $value = array_map('NumberFormatInteger', $value);
        }

        $value = join($joiner, $value);
    }
    else if( $numeric )
    {
        $value = NumberFormatInteger($value);
    }

    $overflow = strlen($value) > 40;
    $value = preg_replace(array('~^(http(s)?://[\w-]+\.[\w-]+(\S+)?)$~i',
                                '~^([\w\d][\w\d\,\.\-]*\@([\w\d\-]+\.)+([a-zA-Z]+))$~i'),
                          array('<a href="$1" target="_blank">$1</a>',
                                '<a href="mailto:$1" target="_blank">$1</a>'),
                          $value);

    switch($type)
    {
        case Form_Field::CHECKBOX:
            $value = '<span style="vertical-align: middle">' .
                     '<img src="images/' . ($value ? 'approve' : 'reject') . '-16x16.png" border="0" />' .
                     '</span>';
            break;

        default:
            $value = '<span>' .
                     (!empty($link) ? '<a href="'.$link.'">'.$value.'</a>' : $value) .
                     '</span>';
            break;

    }

    return '<span class="resizeable-column">' .
           '<b' . ($overflow ? ' class="clickable overflow"' : '') . '>' . String::Truncate($label, 18) . ':</b> ' .
           $value .
           '</span>';
}

function GetTimezones()
{
    static $locations = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
    static $timezones = array();

    if( count($timezones) < 1 )
    {
        $zones = timezone_identifiers_list();

        foreach( $zones as $zone )
        {
            if( strpos($zone, '/') === false )
            {
                continue;
            }

            $parts = explode('/', $zone);

            if( in_array($parts[0], $locations) )
            {
                $timezones[] = $zone;
            }
        }
    }

    return $timezones;
}

function ProgressBarUpdate($id, $percent, $text = 'percent')
{
    static $buffering;

    if( !isset($buffering) )
    {
        $buffering = @ini_get('output_buffering');
    }

    if( $text == 'percent' )
    {
        $text = sprintf('%d%%', $percent);
    }

    echo "<script language=\"JavaScript\">" .
         "window.parent.\$('#$id').progressbar('option', 'value', $percent);" . String::NEWLINE_UNIX .
         "window.parent.\$('#$id').progressbar('text', '$text');" . String::NEWLINE_UNIX .
         "</script>";

    // If output buffering is enabled, send enough characters
    if( $buffering )
    {
        echo str_repeat('x', $buffering);
    }

    flush();
}

function ProgressBarShow($id)
{
    static $buffering;

    if( !isset($buffering) )
    {
        $buffering = @ini_get('output_buffering');
    }

    echo "<script language=\"JavaScript\">" .
         "window.parent.\$('#$id').show();" . String::NEWLINE_UNIX .
         "</script>";

    // If output buffering is enabled, send enough characters
    if( $buffering )
    {
        echo str_repeat('x', $buffering);
    }

    flush();
}

function ProgressBarHide($id, $growl = null, $id_button = null, $reset = true)
{
    static $buffering;

    if( !isset($buffering) )
    {
        $buffering = @ini_get('output_buffering');
    }

    echo "<script language=\"JavaScript\">" .
         "window.parent.\$('#$id').hide();" . String::NEWLINE_UNIX .
         "window.parent.\$('img.activity').hide();" . String::NEWLINE_UNIX .
         ($reset ?
         "window.parent.\$('#$id').progressbar('option', 'value', 0);" . String::NEWLINE_UNIX .
         "window.parent.\$('#$id').progressbar('text', '');" . String::NEWLINE_UNIX : '') .
         (!empty($growl) ? "window.parent.\$.growl.message('$growl');" . String::NEWLINE_UNIX : '') .
         (!empty($id_button) ? "window.parent.\$('#$id_button').removeAttr('disabled');" . String::NEWLINE_UNIX : '') .
         "</script>";

    // If output buffering is enabled, send enough characters
    if( $buffering )
    {
        echo str_repeat('x', $buffering);
    }

    flush();
}

function Execute($function, $default = 'tbxIndexShow')
{
    if( empty($function) )
    {
        call_user_func($default);
        return;
    }
    else if( preg_match('/^(tbx[a-zA-Z0-9_]+)(\((.*?)\))?/', $function, $matches) )
    {
        $function = $matches[1];
        $arguments = isset($matches[3]) ? explode(',', $matches[3]) : array();

        if( function_exists($function) )
        {
            call_user_func_array($function, $arguments);
            return;
        }
    }

    throw new BaseException('Not a valid TubeX function', $function);
}

function IncludeJavascript($file)
{
    if( is_file($file) )
    {
        echo '<script type="text/javascript" language="JavaScript">' . String::NEWLINE_UNIX;
        include($file);
        echo '</script>' . String::NEWLINE_UNIX;
    }
}

function PrepareSearchAndSortFields(&$search_fields, &$sort_fields, $xtable)
{
    $schema = GetDBSchema();

    $search_fields = array(array('column' => Form_Field::OPTGROUP, 'label' => $xtable->naming->textUpper));
    $sort_fields = array(array('column' => Form_Field::OPTGROUP, 'label' => $xtable->naming->textUpper));

    // Base table
    foreach( $xtable->xpath('./columns/column') as $xcolumn )
    {
        $col = $xtable->name . '.' . $xcolumn->name;
        $label = $xcolumn->label;

        if( $xcolumn->admin->search->val() )
        {
            $item = array('column' => $col, 'label' => $label);
            $autocomplete = $xcolumn->autocomplete;

            if( !empty($autocomplete) )
            {
                $item['attr'] = 'acomplete="'.$autocomplete->val().'"';
            }

            $search_fields[] = $item;
        }

        if( $xcolumn->admin->sort->val() )
        {
            $sort_fields[] = array('column' => $col, 'label' => $label);
        }
    }

    // Join tables
    foreach( $xtable->xpath('./join') as $join )
    {
        $xjoin_table = $schema->el('//table[name="'.$join->table.'"]');

        if( $xjoin_table->el('./columns/column/admin[search="true"]') )
        {
            $search_fields[] = array('column' => Form_Field::OPTGROUP, 'label' => $xjoin_table->naming->textUpper);
        }

        if( $xjoin_table->el('./columns/column/admin[sort="true"]') )
        {
            $sort_fields[] = array('column' => Form_Field::OPTGROUP, 'label' => $xjoin_table->naming->textUpper);
        }

        foreach( $xjoin_table->xpath('./columns/column') as $xcolumn )
        {
            $col = $xjoin_table->name . '.' . $xcolumn->name;
            $label = $xcolumn->label->val();

            if( $xcolumn->admin->search->val() )
            {
                $item = array('column' => $col, 'label' => $label);
                $autocomplete = $xcolumn->autocomplete;

                if( !empty($autocomplete) )
                {
                    $item['attr'] = 'acomplete="'.$autocomplete->val().'"';
                }

                $search_fields[] = $item;
            }

            if( $xcolumn->admin->sort->val() )
            {
                $sort_fields[] = array('column' => $col, 'label' => $label);
            }
        }
    }
}


?>