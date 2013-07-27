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


class Cache_MySQL
{

    public static function IsCached($name)
    {
        $DB = GetDB();
        return $DB->QueryCount('SELECT COUNT(*) FROM `tbx_stored_value` WHERE `name`=?', array($name)) > 0;
    }

    public static function Cache($name, $value, $overwrite = true)
    {
        $DB = GetDB();

        if( $overwrite || $DB->QueryCount('SELECT COUNT(*) FROM `tbx_stored_value` WHERE `name`=?', array($name)) == 0)
        {
            $DB->Update('REPLACE INTO `tbx_stored_value` VALUES (?,?)', array($name, $value));
        }
    }

    public static function Remove($name)
    {
        $DB = GetDB();
        $DB->Update('DELETE FROM `tbx_stored_value` WHERE `name`=?', array($name));
    }

    public static function Get($name)
    {
        $DB = GetDB();
        $row = $DB->Row('SELECT * FROM `tbx_stored_value` WHERE `name`=?', array($name));

        return !empty($row) ? $row['value'] : null;
    }
}

?>