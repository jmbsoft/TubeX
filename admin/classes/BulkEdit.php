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

final class BulkEdit
{
    const ACTION_SET = 'Set';
    const ACTION_APPEND = 'Append';
    const ACTION_PREPEND = 'Prepend';
    const ACTION_ADD = 'Add';
    const ACTION_SUBTRACT = 'Subtract';
    const ACTION_INCREMENT = 'Increment';
    const ACTION_DECREMENT = 'Decrement';
    const ACTION_REPLACE = 'Replace';
    const ACTION_TRIM = 'Trim';
    const ACTION_CLEAR = 'Clear';
    const ACTION_TRUNCATE = 'Truncate';
    const ACTION_UPPERCASE_ALL = 'Uppercase All';
    const ACTION_UPPERCASE_FIRST = 'Uppercase First';
    const ACTION_LOWERCASE_ALL = 'Lowercase All';
    const ACTION_RAW_SQL = 'Raw SQL';

    private function __construct() {}
}

?>