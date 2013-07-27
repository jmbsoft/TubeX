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


class Zip
{

    public static function ExtractEntries($archive, $type)
    {
        $si = ServerInfo::GetCached();

        if( !$si->php_extensions[ServerInfo::EXT_ZIP] )
        {
            throw new BaseException('Sorry, the ZIP extension for PHP is not available on this server');
        }

        $entries = array();

        $zip = zip_open($archive);

        if( !is_resource($zip) )
        {
            throw new BaseException('Invalid zip file; zip error #' . $zip . ' generated');
        }

        while( ($entry = zip_read($zip)) !== false )
        {
            if( !is_resource($entry) )
            {
                throw new BaseException('Invalid zip entry; zip error #' . $zip . ' generated');
            }

            $name = zip_entry_name($entry);

            if( File::Type($name) == $type )
            {
                $entries[basename($name)] = self::ExtractEntry($zip, $entry);
            }
        }

        zip_close($zip);

        return $entries;
    }

    public static function ExtractEntry($zip, $entry)
    {
        zip_entry_open($zip, $entry);
        $data = zip_entry_read($entry, zip_entry_filesize($entry));
        zip_entry_close($entry);

        return $data;
    }
}

?>