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


class Privileges
{
    const VIDEOS         = 0x00000001;
    const VIDEO_COMMENTS = 0x00000002;
    const USERS          = 0x00000004;
    const SPONSORS       = 0x00000008;
    const CATEGORIES     = 0x00000010;
    const BANNERS        = 0x00000020;
    const ADMINISTRATORS = 0x00000040;
    const BLACKLIST      = 0x00000080;
    const SEARCH_TERMS   = 0x00000100;
    const DATABASE       = 0x00000200;
    const TEMPLATES      = 0x00000400;
    const REASONS        = 0x00000800;
    const VIDEO_FEEDS    = 0x00001000;


    // No objects
    private function __construct() { }

    public static function Generate($input)
    {
        $privileges = 0;
        $reflect = new ReflectionClass('Privileges');

        foreach( $input as $key => $value )
        {
            if( preg_match('~^PRIVILEGE_([A-Z0-9_]+)~', $key, $matches) && $value == 1 )
            {
                $matches;
                $privileges |= $reflect->getConstant($matches[1]);
            }
        }

        return $privileges;
    }

    public static function FromType($type)
    {
        $schema = GetDBSchema();
        $xtable = $schema->el('//table[naming/type="'.$type.'"]');
        $privilege = $xtable->privilege;

        $reflect = new ReflectionClass('Privileges');

        return $reflect->getConstant($privilege);
    }

    public static function Check($privilege)
    {
        if( !Authenticate::IsSuperUser() && !(Authenticate::GetPrivileges() & $privilege) )
        {
            if( defined('TUBEX_AJAX') )
            {
                JSON::Error('You do not have the necessary privileges to access this function');
            }
            else
            {
                include_once 'cp-insufficient-privileges.php';
            }

            exit;
        }
    }

    public static function CheckSuper()
    {
        if( !Authenticate::IsSuperUser() )
        {
            if( defined('TUBEX_AJAX') )
            {
                JSON::Error('Only superuser control panel accounts can access this function');
            }
            else
            {
                include_once 'cp-superuser-only.php';
            }

            exit;
        }
    }
}

?>