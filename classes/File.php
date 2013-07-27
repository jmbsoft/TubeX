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

class File
{

    const DEFAULT_EXTENSION = 'txt';

    const TYPE_JPEG = 'jpeg';
    const TYPE_IMAGE = 'image';
    const TYPE_ZIP = 'zip';
    const TYPE_VIDEO = 'video';
    const TYPE_UNKNOWN = 'unknown';

    private static $temp_file_chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');

    public static function Extension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    public static function Type($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        switch($extension)
        {
            case ZIP_EXTENSION:
                return self::TYPE_ZIP;

            case JPG_EXTENSION:
                return self::TYPE_JPEG;

            default:
                if( preg_match('~^' . VIDEO_EXTENSIONS . '$~i', $extension, $matches) )
                {
                    return self::TYPE_VIDEO;
                }
                else if( preg_match('~^' . IMAGE_EXTENSIONS . '$~i', $extension, $matches) )
                {
                    return self::TYPE_IMAGE;
                }
        }

        return self::TYPE_UNKNOWN;
    }

    public static function Temporary($directory, $extension = DEFAULT_EXTENSION, $create = false)
    {
        $directory = Dir::StripTrailingSlash($directory);

        if( !is_dir($directory) )
        {
            throw new BaseException('Not a directory', $directory);
        }

        do
        {
            $filename = '';
            for( $i = 0; $i < 10; $i++ )
            {
                $filename .= self::$temp_file_chars[array_rand(self::$temp_file_chars)];
            }

            $filename = "$directory/$filename.$extension";
        }
        while( file_exists($filename) );

        if( $create )
        {
            self::Create($filename);
        }

        return $filename;
    }

    public static function Sanitize($filename, $force_extension = null)
    {
        $info = pathinfo($filename);
        $filename = $info['filename'];
        $extension = isset($info['extension']) ? $info['extension'] : '';

        $filename = preg_replace('~[^a-z0-9_\-]~i', '', $filename);
        $extension = preg_replace('~[^a-z0-9]~i', '', $extension);

        if( String::IsEmpty($filename) )
        {
            $filename = 'none';
        }

        if( String::IsEmpty($extension) )
        {
            return $filename . ($force_extension ? '.' . $force_extension : '');
        }
        else
        {
            return $filename . '.' . ($force_extension ? $force_extension : $extension);
        }
    }

    public static function Create($filename)
    {
        if( !file_exists($filename) )
        {
            file_put_contents($filename, '', LOCK_EX);
            @chmod($filename, 0666);
        }
    }

    public static function Overwrite($filename, $data)
    {
        file_put_contents($filename, $data, LOCK_EX);
        @chmod($filename, 0666);
    }

    public static function Append($filename, $data)
    {
        if( file_exists($filename) && !is_file($filename) )
        {
            throw new BaseException('Not a file', $filename);
        }

        file_put_contents($filename, $data, LOCK_EX | FILE_APPEND);
        @chmod($filename, 0666);
    }

    public static function ReadLine($filename)
    {
        if( !file_exists($filename) )
        {
            throw new BaseException('File not found', $filename);
        }

        if( !is_file($filename) )
        {
            throw new BaseException('Not a file', $filename);
        }

        $fd = fopen($filename, 'r');
        flock($fd, LOCK_SH);
        $line = trim(fgets($fd));
        flock($fd, LOCK_UN);
        fclose($fd);

        return $line;
    }

    public static function Delete($filename)
    {
        if( is_file($filename) )
        {
            @unlink($filename);
        }
    }
}

?>