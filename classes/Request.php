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

class Request
{
    private static $setup = false;
    private static $post_max_size_exceeded = false;

    public static function Setup()
    {
        if( !self::$setup )
        {
            self::$setup = true;

            // Check if post_max_size was exceeded
            if( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0 )
            {
                self::$post_max_size_exceeded = true;
            }

            if( get_magic_quotes_gpc() == 1 )
            {
                $_GET = String::RemoveSlashes($_GET);
                $_POST = String::RemoveSlashes($_POST);
                $_COOKIE = String::RemoveSlashes($_COOKIE);
            }

            $_REQUEST = array_map(array('String', 'Trim'), array_merge($_GET, $_POST));
        }
    }

    public static function FixFiles()
    {
        $copy = $_FILES;
        $_FILES = array();

        foreach( $copy as $field => $data )
        {
            if( is_array($data['name']) )
            {
                $_FILES[$field] = array();

                foreach( $data['name'] as $i => $value )
                {
                    $_FILES[$field][$i] = array('error' => $data['error'][$i],
                                                'size' => $data['size'][$i],
                                                'tmp_name' => $data['tmp_name'][$i],
                                                'name' => $data['name'][$i],
                                                'type' => $data['type'][$i]);
                }
            }
            else
            {
                $_FILES[$field] = $data;
            }
        }
    }

    public static function PostMaxSizeExceeded()
    {
        return self::$post_max_size_exceeded;
    }

    public static function Get($name)
    {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
    }

    public static function GetSafe($name)
    {
        if( isset($_REQUEST[$name]) )
        {
            return String::HtmlSpecialChars($_REQUEST[$name]);
        }

        return null;
    }
}

?>