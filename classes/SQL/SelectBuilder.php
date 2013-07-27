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

class SQL_SelectBuilder extends SQL_QueryBuilder
{

    private $select_fields = array();

    public function AddSelectField($field)
    {
        $this->select_fields[] = $field;
    }

    public function Generate()
    {
        return 'SELECT' .
               $this->GenerateFieldList() .
               $this->GenerateFrom() .
               $this->GenerateWhere() .
               $this->GenerateOrder() .
               $this->GenerateLimit();
    }

    protected function GenerateFrom()
    {
        $clauses = array();

        $this->binds[] = $this->main_table;
        $clause = ' FROM #';

        foreach( $this->joins as $join )
        {
            list($left_table, $right_table, $join_field, $join_type) = $join;

            $clauses[] = $join_type . ' # USING(#)';
            array_push($this->binds, $right_table, $join_field);
        }

        return $clause . (count($clauses) ? ' ' . join(' ', $clauses) : '');
    }

    protected function GenerateFieldList()
    {
        if( count($this->joins) )
        {
            $this->binds[] = $this->main_table;
            return ' DISTINCT #.*';
        }
        else
        {
            if( count($this->select_fields) )
            {
                foreach( $this->select_fields as $i => $select_field )
                {
                    $this->select_fields[$i] = preg_replace_callback('~([a-z0-9_]+)\.([a-z0-9_]+)~i', array($this, 'CallbackSelectFieldParse'), $select_field);
                }

                return ' ' . join(',', $this->select_fields);
            }
            else
            {
                return ' *';
            }
        }
    }

    private function CallbackSelectFieldParse($matches)
    {
        $this->binds[] = $matches[1];
        $this->binds[] = $matches[2];
        return '#.#';
    }
}

?>