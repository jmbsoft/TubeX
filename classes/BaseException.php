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

class BaseException extends Exception
{

    private $extras = array();

    public function __construct()
    {
        $arguments = func_get_args();
        $message = array_shift($arguments);

        $this->extras = $arguments;
        parent::__construct($message);
    }

    public function getExtras()
    {
        if( count($this->extras) > 0 )
        {
            return "\n" . join("\n", $this->extras);
        }
        else
        {
            return '';
        }
    }

    public function getTraceAsHtml()
    {
        return nl2br(strip_tags($this->getTraceAsString()));
    }
}

?>