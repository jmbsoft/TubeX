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

class Dir
{

    const READ_ALL = 0x00000001;

    const READ_FILES = 0x00000002;

    const READ_DIRECTORIES = 0x00000004;

    private static $temp_dir_chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');

    public static function Create($directory, $mode = 0777, $recursive = true)
    {
        if( !is_dir($directory) && !file_exists($directory) )
        {
            $old_umask = umask(0);
            mkdir($directory, $mode, $recursive);
            umask($old_umask);
        }
    }

    public static function Temporary($mode = 0777)
    {
        do
        {
            $dirname = '';
            for( $i = 0; $i < 10; $i++ )
            {
                $dirname .= self::$temp_dir_chars[array_rand(self::$temp_dir_chars)];
            }

            $dirname = TEMP_DIR . "/$dirname";
        }
        while( is_dir($dirname) );

        self::Create($dirname, $mode);

        return $dirname;
    }

    public static function StripTrailingSlash($directory)
    {
        return preg_replace('~/+$~', '', $directory);
    }

    public static function Remove($directory, $recursive = true)
    {
        $directory = self::StripTrailingSlash($directory);

        if( $recursive )
        {
            foreach( scandir($directory) as $item )
            {
                if( $item == '.' || $item == '..' )
                {
                    continue;
                }

                is_dir("$directory/$item") ? self::Remove("$directory/$item", true) : unlink("$directory/$item");
            }
        }

        rmdir($directory);
    }

    private static function Read($directory, $pattern, $type)
    {
        if( !file_exists($directory) )
        {
            throw new BaseException('File not found', $directory);
        }

        if( !is_dir($directory) )
        {
            throw new BaseException('Not a directory', $directory);
        }

        $contents = array();
        $dh = opendir($directory);
        while( ($file = readdir($dh)) !== false )
        {
            if( $type & self::READ_ALL || (($type & self::READ_FILES) && is_file("$directory/$file")) || (($type & self::READ_DIRECTORIES) && is_dir("$directory/$file")) )
            {
                $contents[] = $file;
            }
        }
        closedir($dh);

        if( $pattern )
        {
            $contents = preg_grep($pattern, $contents);
        }

        return array_values($contents);
    }

    public static function ReadAll($directory, $pattern = null)
    {
        return self::Read($directory, $pattern, self::READ_ALL);
    }

    public static function ReadDirectories($directory, $pattern = null)
    {
        return self::Read($directory, $pattern, self::READ_DIRECTORIES);
    }

    public static function ReadFiles($directory, $pattern = null)
    {
        return self::Read($directory, $pattern, self::READ_FILES);
    }
}

?>