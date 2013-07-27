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

final class Validator_Type
{

    const NONE                   = -1;

    const IS_EMPTY               = 0;

    const NOT_EMPTY              = 1;

    const GREATER                = 2;

    const GREATER_EQ             = 3;

    const LESS                   = 4;

    const LESS_EQ                = 5;

    const VALID_EMAIL            = 6;

    const VALID_HTTP_URL         = 7;

    const LENGTH_LESS            = 8;

    const LENGTH_LESS_EQ         = 9;

    const LENGTH_GREATER         = 10;

    const LENGTH_GREATER_EQ      = 11;

    const IS_ZERO                = 12;

    const IS_ALPHANUM            = 13;

    const IS_NUMERIC             = 14;

    const IS_TRUE                = 15;

    const IS_FALSE               = 16;

    const NOT_TRUE               = 17;

    const NOT_FALSE              = 18;

    const REGEX_MATCH            = 19;

    const REGEX_NO_MATCH         = 20;

    const IS_BETWEEN             = 21;

    const VALID_DATETIME         = 22;

    const VALID_DATE             = 23;

    const VALID_TIME             = 24;

    const CONTAINS               = 25;

    const NOT_CONTAINS           = 26;

    const LENGTH_BETWEEN         = 27;

    const NOT_ZERO               = 28;

    const EQUALS                 = 29;

    const NOT_EQUALS             = 30;

    private function __construct() {}
}

?>