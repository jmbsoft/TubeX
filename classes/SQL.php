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

class SQL
{

    const LIKE             = 1;
    const NOT_LIKE         = 2;
    const EQUALS           = 3;
    const NOT_EQUALS       = 4;
    const STARTS_WITH      = 5;
    const NOT_STARTS_WITH  = 6;
    const ENDS_WITH        = 7;
    const NOT_ENDS_WITH    = 8;
    const BETWEEN          = 9;
    const NOT_BETWEEN      = 10;
    const GREATER          = 11;
    const GREATER_EQ       = 12;
    const LESS             = 13;
    const LESS_EQ          = 14;
    const IN               = 15;
    const NOT_IN           = 16;
    const IS_EMPTY         = 17;
    const NOT_EMPTY        = 18;
    const IS_NULL          = 19;
    const NOT_NULL         = 20;
    const FULLTEXT         = 21;
    const LENGTH_EQ        = 22;
    const LENGTH_GREATER   = 23;
    const LENGTH_LESS      = 24;
    const RLIKE            = 25;
    const NOT_RLIKE        = 26;
    const FULLTEXT_BOOLEAN = 28;

    const TINYINT = 'TINYINT';
    const SMALLINT = 'SMALLINT';
    const MEDIUMINT = 'MEDIUMINT';
    const INT = 'INT';
    const INTEGER = 'INTEGER';
    const BIGINT = 'BIGINT';
    const FLOAT = 'FLOAT';
    const DOUBLE = 'DOUBLE';
    const DECIMAL = 'DECIMAL';
    const NUMERIC = 'NUMERIC';
    const BIT = 'BIT';
    const CHAR = 'CHAR';
    const VARCHAR = 'VARCHAR';
    const TINYTEXT = 'TINYTEXT';
    const TEXT = 'TEXT';
    const MEDIUMTEXT = 'MEDIUMTEXT';
    const LONGTEXT = 'LONGTEXT';
    const BINARY = 'BINARY';
    const VARBINARY = 'VARBINARY';
    const TINYBLOB = 'TINYBLOB';
    const BLOB = 'BLOB';
    const MEDIUMBLOB = 'MEDIUMBLOB';
    const LONGBLOB = 'LONGBLOB';
    const ENUM = 'ENUM';
    const SET = 'SET';
    const DATE = 'DATE';
    const TIME = 'TIME';
    const DATETIME = 'DATETIME';
    const TIMESTAMP = 'TIMESTAMP';
    const YEAR = 'YEAR';

    const LOGICAL_AND = 'AND';

    const LOGICAL_OR = 'OR';

    const JOIN = 'JOIN';

    const JOIN_LEFT = 'LEFT JOIN';

    const JOIN_RIGHT = 'RIGHT JOIN';

    const SORT_ASC = 'ASC';

    const SORT_DESC = 'DESC';
}

?>
