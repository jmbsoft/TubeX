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

class JSON
{

    const FAILURE = 0;

    const SUCCESS = 1;

    const LOGOUT = 2;

    const ERROR = 3;

    private static $status = 'status';

    public static function Success($array = array())
    {
        if( !is_array($array) )
        {
            $array = array('message' => $array);
        }

        $array[self::$status] = self::SUCCESS;
        echo json_encode($array);
    }

    public static function Failure($array = array())
    {
        if( !is_array($array) )
        {
            $array = array('message' => $array);
        }

        $array[self::$status] = self::FAILURE;
        echo json_encode($array);
    }

    public static function Error($array = array())
    {
        if( !is_array($array) )
        {
            $array = array('message' => $array);
        }

        $array[self::$status] = self::ERROR;
        echo json_encode($array);
    }

    public static function Logout()
    {
        $array = array(self::$status => self::LOGOUT);
        echo json_encode($array);
    }
}

?>
