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

class SQL_QueryBuilder
{

    protected $main_table;

    protected $tables = array();

    protected $wheres = array();

    protected $orders = array();

    protected $joins = array();

    protected $limit_offset = null;

    protected $limit_row_count = null;

    protected $binds = array();

    protected $schema;

    public function __construct($table)
    {
        $this->schema = GetDBSchema();
        $this->main_table = $table;
        $this->tables[$table] = $this->schema->el('//table[name="'.$table.'"]');
    }

    protected function ParseOrderField($field)
    {
        $parsed_field = $field;
        $binds = array();

        if( preg_match_all('~([a-z0-9_]+)\.([a-z0-9_]+)~i', $field, $matches, PREG_SET_ORDER) )
        {
            foreach( $matches as $match )
            {
                list($junk, $table, $column) = $match;
                array_push($binds, $table, $column);

                if( !isset($this->tables[$table]) )
                {
                    $this->tables[$table] = $this->schema->el('//table[name="'.$table.'"]');
                    $join = $this->tables[$this->main_table]->el('.//join[table="'.$table.'"]');

                    if( empty($join) )
                    {
                        throw new BaseException('Could not determine the field on which to join the two tables', $this->main_table, $table);
                    }

                    $this->AddJoin($this->main_table, $table, $join->foreign, SQL::JOIN);
                }
            }

            $parsed_field = preg_replace('~([a-z0-9_]+)\.([a-z0-9_]+)~i', '#.#', $parsed_field);
        }

        return array($parsed_field, $binds);
    }

    protected function ParseField($field)
    {
        $connector = null;

        if( preg_match('~^([a-z0-9_]+)\.([a-z0-9_]+)$~i', $field, $matches) )
        {
            list($junk, $table, $field) = $matches;
        }
        else
        {
            throw new BaseException('The supplied database field is not in the proper TABLE.FIELD format', $field);
        }

        if( !isset($this->tables[$table]) )
        {
            $this->tables[$table] = $this->schema->el('//table[name="'.$table.'"]');
            $join = $this->tables[$this->main_table]->el('.//join[table="'.$table.'"]');

            if( empty($join) )
            {
                throw new BaseException('Could not determine the field on which to join the two tables', $this->main_table, $table);
            }

            $this->AddJoin($this->main_table, $table, $join->foreign, SQL::JOIN);
        }

        return array($table, $field);
    }

    protected function AutoInterpretWhere($where)
    {
        list($table, $field, $operator, $value, $logical_operator) = $where;

        // Don't auto-interpret fulltext searches
        if( $operator == SQL::FULLTEXT || $operator == SQL::FULLTEXT_BOOLEAN )
        {
            return $where;
        }

        $field_type = SQL::TEXT;
        if( preg_match('~^([a-z]+)~i', $this->tables[$table]->el('.//column[name="'.$field.'"]/definition')->val(), $matches) )
        {
            $field_type = $matches[1];
        }

        switch($operator)
        {
            case SQL::BETWEEN:
            case SQL::NOT_BETWEEN:
                if( !is_array($value) )
                {
                    $value = explode(',', $value);
                }

                if( count($value) < 2 )
                {
                    $value[1] = $value[0];
                }

                if( $field_type == SQL::DATETIME )
                {
                    if( preg_match(RegEx::MYSQL_DATE, $value[0]) )
                    {
                        $value[0] = $value[0] . ' 00:00:00';
                    }

                    if( preg_match(RegEx::MYSQL_DATE, $value[1]) )
                    {
                        $value[1] = $value[1] . ' 23:59:59';
                    }
                }
                break;

            case SQL::IN:
            case SQL::NOT_IN:
                if( !is_array($value) )
                {
                    $value = explode(',', $value);
                }
                break;

            case SQL::EQUALS:
            case SQL::NOT_EQUALS:
                if( $field_type == SQL::DATETIME && preg_match(RegEx::MYSQL_DATE, $value) )
                {
                    $value = array($value . ' 00:00:00', $value . ' 23:59:59');
                    $operator = ($operator == SQL::EQUALS) ? SQL::BETWEEN : SQL::NOT_BETWEEN;
                }
                break;

            case SQL::GREATER_EQ:
            case SQL::GREATER:
            case SQL::LESS:
            case SQL::LESS_EQ:
                if( $field_type == SQL::DATETIME && preg_match(RegEx::MYSQL_DATE, $value) )
                {
                    $value = $value . ' 00:00:00';
                }
                break;
        }

        if( $field_type == SQL::TINYINT )
        {
            switch(strtolower($value))
            {
                case 'yes':
                case 'true':
                    $value = 1;
                    break;

                case 'no':
                case 'false':
                    $value = 0;
                    break;
            }
        }

        return array($table, $field, $operator, $value, $logical_operator);
    }

    public function AddWhere($field, $operator, $value, $logical_operator = null, $skip_empty = false)
    {
        // No need to process empty items
        if( $skip_empty && $operator != SQL::IS_EMPTY && $operator != SQL::NOT_EMPTY && String::IsEmpty($value) )
        {
            return;
        }

        // Make sure the previous where item had a logical operator, and if not set to the default of AND
        if( ($end = count($this->wheres)) > 0 )
        {
            $end--;

            if( empty($this->wheres[$end][4]) )
            {
                $this->wheres[$end][4] = SQL::LOGICAL_AND;
            }
        }

        list($table, $field) = $this->ParseField($field);
        $this->wheres[] = array($table, $field, $operator, $value, $logical_operator);
    }

    function AddFulltextWhere($fields, $operator, $value, $skip_empty = false)
    {
        // No need to process empty items
        if( $skip_empty && String::IsEmpty($value) )
        {
            return;
        }

        foreach( $fields as $field )
        {
            list($table, $junk) = $this->ParseField($field);
        }

        $this->wheres[] = array($table, $fields, $operator, $value, SQL::LOGICAL_AND);
    }

    public function AddOrder($field, $direction = SQL::SORT_ASC)
    {
        if( $direction != SQL::SORT_ASC && $direction != SQL::SORT_DESC )
        {
            $direction = SQL::SORT_ASC;
        }

        list($parsed_field, $binds_field) = $this->ParseOrderField($field);
        $this->orders[] = array($parsed_field, $binds_field, $direction);
    }

    public function AddJoin($left_table, $right_table, $join_field, $join_type)
    {
        if( !isset($this->tables[$right_table]) )
        {
            $this->tables[$right_table] = $this->schema->el('//table[name="'.$right_table.'"]');
        }

        $this->joins[] = array($left_table, $right_table, $join_field, $join_type);
    }

    public function SetLimit($offset, $row_count = null)
    {
        $this->limit_offset = $offset;
        $this->limit_row_count = $row_count;
    }

    protected function GenerateWhere()
    {
        $paren = false;
        $clause = null;

        if( ($end = count($this->wheres)) < 1 )
        {
            return $clause;
        }

        // Make sure the last item does not have a logical operator
        $this->wheres[--$end][4] = null;

        foreach( $this->wheres as $where )
        {

            list($table, $field, $operator, $value, $logical_operator) = $this->AutoInterpretWhere($where);

            if( $logical_operator == SQL::LOGICAL_OR && !$paren )
            {
                $paren = true;
                $clause .= '(';
            }

            switch($operator)
            {
                case SQL::FULLTEXT:
                    $clause .= 'MATCH(' . join(',', array_fill(0, count($field), '#.#')) . ') AGAINST (?)';

                    foreach( $field as $f )
                    {
                        list($t, $f) = explode('.', $f);
                        array_push($this->binds, $t, $f);
                    }

                    $this->binds[] = $value;
                    break;

                case SQL::FULLTEXT_BOOLEAN:
                    $clause .= 'MATCH(' . join(',', array_fill(0, count($field), '#.#')) . ') AGAINST (? IN BOOLEAN MODE)';

                    foreach( $field as $f )
                    {
                        list($t, $f) = explode('.', $f);
                        array_push($this->binds, $t, $f);
                    }

                    $this->binds[] = $value;
                    break;

                case SQL::LIKE:
                    $clause .= '#.# LIKE ?';
                    array_push($this->binds, $table, $field, '%'.$value.'%');
                    break;

                case SQL::NOT_LIKE:
                    $clause .= '#.# NOT LIKE ?';
                    array_push($this->binds, $table, $field, '%'.$value.'%');
                    break;

                case SQL::EQUALS:
                    $clause .= '#.#=?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::NOT_EQUALS:
                    $clause .= '#.#!=?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::STARTS_WITH:
                    $clause .= '#.# LIKE ?';
                    array_push($this->binds, $table, $field, $value.'%');
                    break;

                case SQL::NOT_STARTS_WITH:
                    $clause .= '#.# NOT LIKE ?';
                    array_push($this->binds, $table, $field, $value.'%');
                    break;

                case SQL::ENDS_WITH:
                    $clause .= '#.# LIKE ?';
                    array_push($this->binds, $table, $field, '%'.$value);
                    break;

                case SQL::NOT_ENDS_WITH:
                    $clause .= '#.# NOT LIKE ?';
                    array_push($this->binds, $table, $field, '%'.$value);
                    break;

                case SQL::BETWEEN:
                    $clause .= '#.# BETWEEN ? AND ?';
                    array_push($this->binds, $table, $field, $value[0], $value[1]);
                    break;

                case SQL::NOT_BETWEEN:
                    $clause .= '#.# NOT BETWEEN ? AND ?';
                    array_push($this->binds, $table, $field, $value[0], $value[1]);
                    break;

                case SQL::GREATER:
                    $clause .= '#.#>?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::GREATER_EQ:
                    $clause .= '#.#>=?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::LESS:
                    $clause .= '#.#<?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::LESS_EQ:
                    $clause .= '#.#<=?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::IN:
                    $clause .= '#.# IN (' . join(',',array_fill(0, count($value), '?')) . ')';
                    array_push($this->binds, $table, $field);
                    $this->binds = array_merge($this->binds, $value);
                    break;

                case SQL::NOT_IN:
                    $clause .= '#.# NOT IN (' . join(',',array_fill(0, count($value), '?')) . ')';
                    array_push($this->binds, $table, $field);
                    $this->binds = array_merge($this->binds, $value);
                    break;

                case SQL::IS_EMPTY:
                    $clause .= '(#.# IS NULL OR #.#=?)';
                    array_push($this->binds, $table, $field, $table, $field, '');
                    break;

                case SQL::NOT_EMPTY:
                    $clause .= '(#.# IS NOT NULL AND #.#!=?)';
                    array_push($this->binds, $table, $field, $table, $field, '');
                    break;

                case SQL::IS_NULL:
                    $clause .= '#.# IS NULL';
                    array_push($this->binds, $table, $field);
                    break;

                case SQL::NOT_NULL:
                    $clause .= '#.# IS NOT NULL';
                    array_push($this->binds, $table, $field);
                    break;

                case SQL::LENGTH_EQ:
                    $clause .= 'CHAR_LENGTH(#.#)=?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::LENGTH_GREATER:
                    $clause .= 'CHAR_LENGTH(#.#)>?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::LENGTH_LESS:
                    $clause .= 'CHAR_LENGTH(#.#)<?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::RLIKE:
                    $clause .= '#.# RLIKE ?';
                    array_push($this->binds, $table, $field, $value);
                    break;

                case SQL::NOT_RLIKE:
                    $clause .= '#.# NOT RLIKE ?';
                    array_push($this->binds, $table, $field, $value);
                    break;
            }

            if( $logical_operator == SQL::LOGICAL_AND && $paren )
            {
                $paren = false;
                $clause .= ')';
            }

            if( $logical_operator )
            {
                $clause .= ' ' . $logical_operator . ' ';
            }
        }

        if( $paren )
        {
            $clause .= ')';
        }

        return ($clause ? ' WHERE ' . $clause : '');
    }

    protected function GenerateOrder()
    {
        $clauses = array();

        foreach( $this->orders as $order )
        {
            list($parsed_field, $binds_field, $direction) = $order;

            $clauses[] = $parsed_field . ' ' . $direction;
            $this->binds = array_merge($this->binds, $binds_field);
        }

        return (count($clauses) ? ' ORDER BY ' . join(',', $clauses) : '');
    }

    protected function GenerateLimit()
    {
        if( $this->limit_offset && $this->limit_row_count )
        {
            return ' LIMIT ' . $this->limit_offset . ',' . $this->limit_row_count;
        }
        else if( $this->limit_offset )
        {
            return ' LIMIT ' . $this->limit_offset;
        }

        return '';
    }

    public function Binds()
    {
        return $this->binds;
    }
}

?>