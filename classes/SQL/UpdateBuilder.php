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

class SQL_UpdateBuilder extends SQL_QueryBuilder
{

    private $sets = array();

    function AddSet($field, $new_value, $binds = array())
    {
        list($table, $field) = $this->ParseField($field);
        $this->sets[] = array($table, $field, $new_value, $binds);
    }

    public function Generate()
    {
        return 'UPDATE' .
               $this->GenerateTableReferences() .
               $this->GenerateSet() .
               $this->GenerateWhere();
    }

    protected function AutoInterpretSet($set)
    {
        list($table, $field, $new_value, $binds) = $set;

        $field_type = SQL::TEXT;
        if( preg_match('~^([a-z]+)~i', $this->tables[$table]->el('.//column[name="'.$field.'"]/definition')->val(), $matches) )
        {
            $field_type = $matches[1];
        }

        if( $field_type == SQL::TINYINT )
        {
            foreach( $binds as $i => $bind )
            {
                switch(strtolower($binds[$i]))
                {
                    case 'yes':
                    case 'true':
                        $binds[$i] = 1;
                        break;

                    case 'no':
                    case 'false':
                        $binds[$i] = 0;
                        break;
                }
            }
        }

        return array($table, $field, $new_value, $binds);
    }

    protected function GenerateSet()
    {
        $sets = array();
        foreach( $this->sets as $set )
        {
            list($table, $field, $new_value, $binds) = $this->AutoInterpretSet($set);

            $sets[] = '#.#=' . $new_value;
            $this->binds[] = $table;
            $this->binds[] = $field;

            foreach( $binds as $bind )
            {
                $this->binds[] = $bind;
            }
        }

        return ' SET ' . join(',', $sets);
    }

    protected function GenerateTableReferences()
    {
        $clauses = array();

        $this->binds[] = $this->main_table;
        $clause = ' #';

        foreach( $this->joins as $join )
        {
            list($left_table, $right_table, $join_field, $join_type) = $join;

            // Force left join so tables get updated even if an item does not exist in a joined table
            //$clauses[] = $join_type . ' # USING(#)';
            $clauses[] = 'LEFT JOIN # USING(#)';
            array_push($this->binds, $right_table, $join_field);
        }

        return $clause . (count($clauses) ? ' ' . join(' ', $clauses) : '');
    }
}

?>