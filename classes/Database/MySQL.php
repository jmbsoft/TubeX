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


class Database_MySQL
{

    private static $ERROR_CONNECT = 'Could not connect to MySQL database';
    private static $ERROR_SELECT = 'Could not select database';
    private static $ERROR_QUERY = 'Database query execution failed';
    private static $ERROR_NOT_CONNECTED = 'Not connected to the MySQL database server';
    private static $ERROR_VERSION = 'Could not determine the MySQL version number';
    private static $ERROR_NUM_ROWS = 'Could not retrieve the number of rows from the result set';
    private static $ERROR_FREE = 'Could not free the result set';

    private $connected = false;

    private $handle = false;

    private $username;

    private $password;

    private $database;

    private $hostname;

    public function __construct($user, $pass, $db, $host)
    {
        $this->username = $user;
        $this->password = $pass;
        $this->database = $db;
        $this->hostname = $host;

        $this->Connect();
    }

    public function __destruct()
    {
        $this->Disconnect();
    }

    public function Connect()
    {
        if( !$this->connected )
        {
            $this->handle = @mysql_connect($this->hostname, $this->username, $this->password, true);

            if( $this->handle === false )
            {
                throw new BaseException(self::$ERROR_CONNECT, mysql_error());
            }

            $this->connected = true;

            if( mysql_select_db($this->database, $this->handle) === false )
            {
                throw new BaseException(self::$ERROR_SELECT, mysql_error($this->handle));
            }

            $this->Update('SET `wait_timeout`=86400');
			$this->Update('SET `interactive_timeout`=86400');
        }
    }

    public function Disconnect()
    {
        if( $this->connected )
        {
            mysql_close($this->handle);
            $this->connected = false;
        }
    }

    public static function Now()
    {
        return date('Y-m-d H:i:s');
    }

    public function Version()
    {
        if( $this->connected )
        {
            $result = mysql_query('SELECT VERSION()', $this->handle);

            if( $result === false )
            {
                throw new BaseException(self::$ERROR_VERSION, mysql_error($this->handle));
            }

            $row = mysql_fetch_row($result);
            mysql_free_result($result);

            $version = array('full' => $row[0]);
            if( preg_match('~^(\d+)\.(\d+)\.(\d+)~', $row[0], $matches) )
            {
                $version['major'] = $matches[1];
                $version['minor'] = $matches[2];
                $version['patch'] = $matches[3];
            }

            return $version;
        }
        else
        {
            throw new BaseException(self::$ERROR_NOT_CONNECTED);
        }
    }

    public function Query($query, $binds = array())
    {
        $query = $this->Prepare($query, $binds);
        $result = mysql_query($query, $this->handle);

        if( $result === false )
        {
            throw new BaseException(self::$ERROR_QUERY, mysql_error($this->handle), $query);
        }

        return $result;
    }

    public function QuerySingleColumn($query, $binds = array())
    {
        $query = $this->Prepare($query, $binds);
        $result = mysql_query($query, $this->handle);

        if( $result === false )
        {
            throw new BaseException(self::$ERROR_QUERY, mysql_error($this->handle), $query);
        }

        $row = mysql_fetch_array($result, MYSQL_NUM);
        mysql_free_result($result);

        if( is_array($row) )
        {
            return $row[0];
        }
        else
        {
            return false;
        }
    }

    public function QueryCount($query, $binds = array(), $primary_key = null)
    {
        $count = 0;
        $count_query = preg_replace(array('~SELECT DISTINCT #\.\* FROM~i',
                                          '~SELECT \* FROM~i',
                                          '~ ORDER BY (.*?)$~i'),
                                    array("SELECT COUNT(DISTINCT #.`$primary_key`) FROM",
                                          'SELECT COUNT(*) FROM',
                                          ''),
                                    $query);

        if( stristr($count_query, 'GROUP BY') )
        {
            $temp_result = $this->Query($count_query, $binds);
            $count = $this->NumRows($temp_result);
            $this->Free($temp_result);
        }
        else
        {
            $count = $this->QuerySingleColumn($count_query, $binds);
        }

        return $count;
    }

    public function QueryWithPagination($query, $binds = array(), $page = 1, $per_page = 20, $primary_key = null)
    {
        $result = array();

        // Get total number of results
        $count_query = preg_replace(array('~SELECT DISTINCT #\.\* FROM~i',
                                          '~SELECT \*.*? FROM~i',
                                          '~ ORDER BY (.*?)$~i'),
                                    array("SELECT COUNT(DISTINCT #.`$primary_key`) FROM",
                                          'SELECT COUNT(*) FROM',
                                          ''),
                                    $query);

        if( stristr($count_query, 'GROUP BY') )
        {
            $temp_result = $this->Query($count_query, $binds);
            $result['total'] = $this->NumRows($temp_result);
            $this->Free($temp_result);
        }
        else
        {
            $result['total'] = $this->QuerySingleColumn($count_query, $binds);
        }

        // Calculate pagination
        $result['formatted']['total'] = $result['total'];
        $result['formatted']['pages'] = $result['pages'] = ceil($result['total']/$per_page);
        $result['page'] = min(max($page, 1), $result['pages']);
        $result['limit'] = max(($result['page'] - 1) * $per_page, 0);
        $result['formatted']['start'] = $result['start'] = max(($result['page'] - 1) * $per_page + 1, 0);
        $result['formatted']['end'] = $result['end'] = min($result['start'] - 1 + $per_page, $result['total']);
        $result['prev'] = ($result['page'] > 1);
        $result['next'] = ($result['end'] < $result['total']);

        if( class_exists('config') )
        {
            $result['formatted']['pages'] = NumberFormatInteger($result['formatted']['pages']);
            $result['formatted']['start'] = NumberFormatInteger($result['formatted']['start']);
            $result['formatted']['end'] = NumberFormatInteger($result['formatted']['end']);
            $result['formatted']['total'] = NumberFormatInteger($result['formatted']['total']);
        }

        if( $result['next'] )
        {
            $result['next_page'] = $result['page'] + 1;
        }

        if( $result['prev'] )
        {
            $result['prev_page'] = $result['page'] - 1;
        }

        if( $result['total'] > 0 )
        {
            $result['handle'] = $this->Query("$query LIMIT {$result['limit']},{$per_page}", $binds);
            $result['numrows'] = $this->NumRows($result['handle']);
        }
        else
        {
            $result['handle'] = false;
            $result['numrows'] = 0;
        }

        return $result;
    }

    public function FetchAll($query, $binds = array(), $field = null)
    {
        $result = null;
        if( is_bool($query) )
        {
            return array();
        }
        else if( is_resource($query) )
        {
            $result = $query;
        }
        else
        {
            $query = $this->Prepare($query, $binds);
            $result = mysql_query($query, $this->handle);

            if( $result === false )
            {
                throw new BaseException(self::$ERROR_QUERY, mysql_error($this->handle), $query);
            }
        }

        $results = array();
        while( $row = mysql_fetch_assoc($result) )
        {
            if( $field )
            {
                $results[$row[$field]] = $row;
            }
            else
            {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function Row($query, $binds = array())
    {
        $query = $this->Prepare($query, $binds);
        $result = mysql_query($query, $this->handle);

        if( $result === false )
        {
            throw new BaseException(self::$ERROR_QUERY, mysql_error($this->handle), $query);
        }

        $row = mysql_fetch_assoc($result);
        mysql_free_result($result);

        return $row;
    }

    public function Update($query, $binds = array())
    {
        $query = $this->Prepare($query, $binds);
        $result = mysql_query($query, $this->handle);

        if( $result === false )
        {
            throw new BaseException(self::$ERROR_QUERY, mysql_error($this->handle), $query);
        }

        return mysql_affected_rows($this->handle);
    }

    public function NumRows($result)
    {
        if( ($rows = @mysql_num_rows($result)) === false )
        {
            throw new BaseException(self::$ERROR_NUM_ROWS, mysql_error($this->handle));
        }

        return $rows;
    }

    public function NextRow($result)
    {
        return mysql_fetch_assoc($result);
    }

    public function Free($result)
    {
        if( @mysql_free_result($result) === false )
        {
            throw new BaseException(self::$ERROR_FREE, mysql_error($this->handle));
        }
    }

    public function LastInsertId()
    {
        return mysql_insert_id($this->handle);
    }

    public function GetTables()
    {
        $tables = array();
        $result = $this->Query('SHOW TABLES');
        $field = mysql_field_name($result, 0);

        while( $row = $this->NextRow($result) )
        {
            $tables[$row[$field]] = $row[$field];
        }

        $this->Free($result);

        return $tables;
    }

    public function GetColumns($table, $as_hash = false, $with_backticks = false)
    {
        $columns = array();
        $result = $this->Query('DESCRIBE #', array($table));
        $field = mysql_field_name($result, 0);

        while( $column = $this->NextRow($result) )
        {
            if( $as_hash )
            {
                $columns[$column[$field]] = $with_backticks ? "`{$column[$field]}`" : $column[$field];
            }
            else
            {
                $columns[] = $with_backticks ? "`{$column[$field]}`" : $column[$field];
            }
        }

        $this->Free($result);

        return $columns;
    }

    public function DumpTables($tables, $filename)
    {
        $fd = fopen($filename, 'w');

        if( $fd !== false )
        {
            foreach( $tables as $table )
            {
                $row = $this->Row('SHOW CREATE TABLE #', array($table));
                $create = str_replace(array("\r", "\n"), '', $row['Create Table']);

                fwrite($fd, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($fd, "$create;\n");
                fwrite($fd, "LOCK TABLES `$table` WRITE;\n");
                fwrite($fd, "ALTER TABLE `$table` DISABLE KEYS;\n");

                $result = mysql_unbuffered_query("SELECT * FROM `$table`", $this->handle);
                while( $row = mysql_fetch_row($result) )
                {
                    foreach( $row as $i => $value )
                    {
                        if( $value === null )
                        {
                            $row[$i] = 'NULL';
                        }
                        else
                        {
                            $row[$i] = "'" . mysql_real_escape_string($value, $this->handle) . "'";
                        }
                    }
                    fwrite($fd, "INSERT INTO `$table` VALUES (" . join(",", $row) . ");\n");
                }
                mysql_free_result($result);

                fwrite($fd, "UNLOCK TABLES;\n");
                fwrite($fd, "ALTER TABLE `$table` ENABLE KEYS;\n");
            }

            @chmod($filename, 0666);
            fclose($fd);
        }
    }

    public function RestoreTables($filename)
    {
        $buffer = '';
        $fd = fopen($filename, 'r');

        if( $fd )
        {
            while( !feof($fd) )
            {
                $line = trim(fgets($fd));

                // Skip comments and empty lines
                if( empty($line) || preg_match('~^(/\*|--)~', $line) )
                {
                    continue;
                }

                if( !preg_match('~;$~', $line) )
                {
                    $buffer .= $line;
                    continue;
                }

                // Remove trailing ; character
                $line = preg_replace('~;$~', '', $line);

                $buffer .= $line;

                mysql_query($buffer, $this->handle);

                $buffer = '';
            }

            fclose($fd);
        }
    }

    public function Prepare($query, $binds = array())
    {
        if( empty($binds) )
        {
            return $query;
        }
        
        $query_result = '';
        $index = 0;

        $pieces = preg_split('~(\?|#)~', $query, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach( $pieces as $piece )
        {
            if( $piece == '?' )
            {
                if( $binds[$index] === null )
                {
                    $query_result .= 'NULL';
                }

                else
                {
                    $query_result .= "'" . mysql_real_escape_string($binds[$index], $this->handle) . "'";
                }

                $index++;
            }
            else if( $piece == '#' )
            {
                $binds[$index] = str_replace('`', '\`', $binds[$index]);
                $query_result .= "`" . $binds[$index] . "`";
                $index++;
            }
            else
            {
                $query_result .= $piece;
            }
        }

        return $query_result;
    }
}

?>