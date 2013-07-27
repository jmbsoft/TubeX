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

class Form_Field
{

    const TEXT = 'Text';

    const TEXTAREA = 'Textarea';

    const SELECT = 'Select';

    const CHECKBOX = 'Checkbox';

    const RADIO = 'Radio';

    const PASSWORD = 'Password';

    const BUTTON = 'Button';

    const FILE = 'File';

    const OPTGROUP = 'Optgroup';

    private static $text_size = 30;

    private static $password_size = 30;

    private static $textarea_rows = 5;

    private static $textarea_cols = 60;

    public static function ParseAttributes($attributes, $filter = null, $as_string = false)
    {
        $attrs = array();
        $attrs_string = array();

        $filter = explode(',', $filter);

        if( preg_match_all('~([a-z_]+)\s*=\s*["\']?(.*?)["\']?(?=(?:\s*[a-z_]+\s*=)|$)~i', $attributes, $matches, PREG_SET_ORDER) )
        {
            foreach( $matches as $match )
            {
                $name = strtolower(trim($match[1]));
                $value = trim($match[2]);

                if( !in_array($name, $filter) )
                {
                    $attrs[$name] = $value;
                    $attrs_string[] = $name . '="' . $value . '"';
                }
            }
        }

        return $as_string ? join(' ', $attrs_string) : $attrs;
    }

    public static function GenerateFromCustom($type)
    {
        $DB = GetDB();
        $schema = GetDBSchema();
        $xtable = $schema->el('//table[naming/type="' . $type . '"]');
        $primary_key = $xtable->columns->primaryKey->val();
        $custom_schema_table = $xtable->custom->val() . '_schema';
        $html = '';

        $result = $DB->Query('SELECT * FROM # ORDER BY `field_id`', array($custom_schema_table));
        while( $field = $DB->NextRow($result) )
        {
            switch($field['type'])
            {
                case self::TEXT:
                    $html .= '<div class="field">' .
                             '  <label>' . $field['label'] . ':</label>' .
                             '  <span class="field-container">' .
                             '    <input type="text" size="60" name="' . $field['name'] . '" value="' . Request::Get($field['name']) . '" />' .
                             '  </span>' .
                             '</div>';
                    break;

                case self::TEXTAREA:
                    $html .= '<div class="field">' .
                             '  <label>' . $field['label'] . ':</label>' .
                             '  <span class="field-container">' .
                             '    <textarea name="' . $field['name'] . '" rows="5" cols="80">' . Request::Get($field['name']) . '</textarea>' .
                             '  </span>' .
                             '</div>';
                    break;

                case self::SELECT:
                    $html .= '<div class="field">' .
                             '  <label>' . $field['label'] . ':</label>' .
                             '  <span class="field-container">' .
                             '    <select name="' . $field['name'] . '">' .
                             self::OptionsSimple($field['options'], Request::Get($field['name'])) .
                             '    </select>' .
                             '  </span>' .
                             '</div>';
                    break;

                case self::CHECKBOX:
                    $html .= '<div class="field">' .
                             '  <label></label>' .
                             '  <span class="field-container">' .
                             '    <div class="checkbox">' .
                             '      <input type="hidden" name="' . $field['name'] . '" value="' . Request::Get($field['name']) . '" />' .
                             '      ' . $field['label'] .
                             '    </div>' .
                             '  </span>' .
                             '</div>';
                    break;
            }
        }
        $DB->Free($result);

        if( empty($html) )
        {
            $html = '<div class="message-warning text-center">No Custom Fields Have Been Defined</div>';
        }

        return $html;
    }

    public static function Checkbox($name, $checked = 0, $id = null, $class = null, $value = null)
    {
        return '<input ' .
               'type="checkbox" ' .
               'name="' . $name . '" ' .
               (empty($id) ? '' : 'id="' . $id . '" ') .
               (empty($class) ? '' : 'class="' . $class . '" ') .
               'value="' . ($value === null ? 1 : $value) . '" ' .
               ($checked ? 'checked="checked" ' : '') .
               '/>';
    }

    public static function OptionsSimple($options, $selected = null)
    {
        $html = String::BLANK;

        if( !is_array($options) )
        {
            $options = explode(',', String::FormatCommaSeparated($options));
        }

        if( count($options) < 1 )
        {
            return $html;
        }

        foreach( $options as $option )
        {
            $html .= '<option value="' . htmlspecialchars($option, ENT_QUOTES) . '"' . ($option == $selected ? ' selected="selected"' : '') . '>' .
                     htmlspecialchars($option) . '</option>' . String::NEWLINE_UNIX;
        }

        return $html;
    }

    public static function Options($options, $selected = null, $index_value = null, $index_text = null, $max_length = null)
    {
        if( !is_array($options) )
        {
            $options = explode(',', String::FormatCommaSeparated($options));
            $index_value = null;
            $index_text = null;
        }

        $html = null;

        if( count($options) < 1 )
        {
            return $html;
        }

        $in_optgroup = false;
        $multi_select = is_array($selected);
        $simple_options = (empty($index_value) || empty($index_text));
        foreach( $options as $key => $val )
        {
            $value = ($simple_options ? $key : $val[$index_value]);
            $is_selected = (!empty($selected) && (($multi_select && in_array($value, $selected)) || $value == $selected));

            if( $value === self::OPTGROUP )
            {
                if( $in_optgroup )
                {
                    $html .= '</optgroup>' . String::NEWLINE_UNIX;
                }

                $html .= '<optgroup label="'.$val[$index_text].'">' . String::NEWLINE_UNIX;
            }
            else
            {
                $html .= '<option ' .
                         (!$simple_options && isset($val['attr']) ? $val['attr'] . ' ' : '') .
                         'value="' .
                         htmlspecialchars($value) .
                         '"' . ($is_selected ? ' selected="selected"' : '') . '>' .
                         htmlspecialchars(String::Truncate($simple_options ? $val : $val[$index_text], $max_length)) .
                         '</option>' . String::NEWLINE_UNIX;
            }
        }

        if( $in_optgroup )
        {
            $html .= '</optgroup>' . String::NEWLINE_UNIX;
        }

        return $html;
    }
}
?>
