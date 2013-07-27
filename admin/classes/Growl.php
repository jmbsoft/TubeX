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

class Growl
{

    private static $messages = array();

    private static $errors = array();

    private static $warnings = array();

    public static function AddMessage($message)
    {
        if( !string::IsEmpty($message) )
        {
            self::$messages[] = $message;
        }
    }

    public static function AddError($message)
    {
        if( !string::IsEmpty($message) )
        {
            self::$errors[] = $message;
        }
    }

    public static function AddWarning($message)
    {
        if( !string::IsEmpty($message) )
        {
            self::$warnings[] = $message;
        }
    }

    public static function OutputJavascript()
    {
        foreach( self::$messages as $message )
        {
            echo "\$.growl.message('$message');\n";
        }

        foreach( self::$warnings as $message )
        {
            echo "\$.growl.warning('$message');\n";
        }

        foreach( self::$errors as $message )
        {
            echo "\$.growl.error('$message');\n";
        }
    }
}

?>