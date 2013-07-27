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

class String
{

    const NEWLINE_UNIX = "\n";

    const NEWLINE_WINDOWS = "\r\n";

    const NEWLINE_MAC = "\r";

    const BLANK = '';

    public static function ToUTF8($string)
    {
        return is_array($string) ?
               array_map(array('String', 'ToUTF8'), $string) :
               (function_exists('mb_detect_encoding') && mb_detect_encoding($string) == 'UTF-8' && mb_check_encoding($string, 'UTF-8') ? $string : utf8_encode($string));
    }

    public static function FormatNewlines($string, $format = self::NEWLINE_UNIX)
    {
        return preg_replace('~\r\n|\r|\n~', $format, $string);
    }

    public static function IsEmpty($string)
    {
        if( is_array($string) || is_object($string) )
        {
            return false;
        }

        return preg_match('~^\s*$~s', $string) == 1;
    }

    public static function ConvertHtmlEntities($string)
    {
        return is_array($string) ?
               array_map(array('String', 'ConvertHtmlEntities'), $string) :
               htmlentities($string, ENT_QUOTES);
    }

    public static function HtmlSpecialChars($string)
    {
        return is_array($string) ?
               array_map(array('String', 'HtmlSpecialChars'), $string) :
               htmlspecialchars($string, ENT_QUOTES);
    }

    public static function StripTags($string)
    {
        return is_array($string) ?
               array_map(array('String', 'StripTags'), $string) :
               strip_tags($string);
    }

    public static function Truncate($string, $length, $center = false, $append = null)
    {
        if( empty($length) )
        {
            return $string;
        }

        // Set the default append string
        if( $append === null )
        {
            $append = ($center === true) ? ' ... ' : '...';
        }

        // Get some measurements
        $len_string = strlen($string);
        $len_append = strlen($append);

        // If the string is longer than the maximum length, we need to chop it
        if( $len_string > $length )
        {
            // Check if we want to chop it in half
            if( $center === true )
            {
                // Get the lengths of each segment
                $len_start = $length / 2;
                $len_end = $len_string - $len_start;

                // Get each segment
                $seg_start = substr($string, 0, $len_start);
                $seg_end = substr($string, $len_end);

                // Stick them together
                $string = trim($seg_start) . $append . trim($seg_end);
            }
            // Otherwise, just chop the end off
            else
            {
                $string = trim(substr($string, 0, $length - $len_append)) . $append;
            }
        }

        return $string;
    }

    public static function FormatCommaSeparated($string)
    {
        if( strlen($string) < 1 || strstr($string, ',') === false )
        {
            return $string;
        }

        $items = array();

        foreach( explode(',', trim($string)) as $item )
        {
            $items[] = trim($item);
        }

        return join(',', $items);
    }

    public static function ToBool($value)
    {
        if( is_bool($value) )
        {
            return $value;
        }
        else if( is_numeric($value) )
        {
            return $value != 0;
        }
        else if( preg_match('~^true$~i', $value) )
        {
            return true;
        }
        else if( preg_match('~^false$~i', $value) )
        {
            return false;
        }

        return false;
    }

    public static function RemoveSlashes($value)
    {
        return is_array($value) ?
               array_map(array('String', 'RemoveSlashes'), $value) :
               stripslashes($value);
    }

    public static function Trim($value)
    {
        return is_array($value) ?
               array_map(array('String', 'Trim'), $value) :
               trim($value);
    }

    public static function Nullify($string)
    {
        if( self::IsEmpty($string) )
        {
            return null;
        }

        return $string;
    }
}

?>