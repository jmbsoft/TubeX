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


class MatchingItems
{
    private static $TYPE_SELECTED = 'selected';
    private static $TYPE_MATCHING = 'matching';

    private $xtable;

    private $type;

    private $search_form;

    private $calculated_amount;

    public function __construct($xtable)
    {
        $this->xtable = $xtable;
        $this->calculated_amount = null;

        parse_str(Request::Get('search'), $this->search_form);

        if( !isset($this->search_form['selected']) )
        {
            $this->type = self::$TYPE_MATCHING;
        }
        else
        {
            $this->type = self::$TYPE_SELECTED;
        }
    }

    public function __get($variable)
    {
        switch(strtolower($variable))
        {
            case 'ids':
                return $this->Query('ids');

            case 'amount':
                return $this->calculated_amount === null ? $this->Query('amount') : $this->calculated_amount;

            case 'handle':
                return $this->Query('handle');

            case 'formatted_amount':
                return NumberFormatInteger($this->amount);

            case 'type':
                return $this->type;

            case 'message':
                return sprintf('%s %s %s', $this->formatted_amount, $this->type, ($this->amount == 1 ? $this->xtable->naming->textLower : $this->xtable->naming->textLowerPlural));
        }
    }

    public function ApplyDBUpdate($ub)
    {
        $DB = GetDB();
        $search = $this->search_form;
        $table = $this->xtable->name->val();
        $primary_key = $this->xtable->el('.//primaryKey')->val();

        switch($this->type)
        {
            case self::$TYPE_MATCHING:
                // Fulltext searches
                if( isset($search['text_search']) && !String::IsEmpty($search['text_search']) )
                {
                    $columns = array();
                    foreach( $this->xtable->xpath('.//fulltext/column') as $xcolumn )
                    {
                        $columns[] = $table . '.' . $xcolumn->name;
                    }

                    $ub->AddFulltextWhere($columns, $search['text_search_type'], $search['text_search']);
                }

                for( $i = 0; $i < count($search['search_field']); $i++ )
                {
                    $ub->AddWhere($search['search_field'][$i], $search['search_operator'][$i], $search['search_term'][$i], $search['search_connector'][$i], true);
                }
                break;

            case self::$TYPE_SELECTED:
                $ub->AddWhere("$table.$primary_key", SQL::IN, $search['search_term']);
                break;
        }

        $this->calculated_amount = $DB->Update($ub->Generate(), $ub->Binds());
    }

    public function ApplyFunction($function)
    {
        $DB = GetDB();
        $this->calculated_amount = 0;

        $result = $this->handle;
        while( $row = $DB->NextRow($result) )
        {
            call_user_func($function, $row, $this->xtable);
            $this->calculated_amount++;
        }
        $DB->Free($result);
    }

    public function SetCalculatedAmount($amount)
    {
        $this->calculated_amount = $amount;
    }

    private function Query($query)
    {
        $DB = GetDB();
        $xnaming = $this->xtable->naming;
        $table = $this->xtable->name->val();
        $primary_key = $this->xtable->el('.//primaryKey')->val();
        $search = $this->search_form;
        $s = new SQL_SelectBuilder($table);

        switch($this->type)
        {
            case self::$TYPE_MATCHING:
                // Fulltext searches
                if( isset($search['text_search']) && !String::IsEmpty($search['text_search']) )
                {
                    $columns = array();
                    foreach( $this->xtable->xpath('.//fulltext/column') as $xcolumn )
                    {
                        $columns[] = $table . '.' . $xcolumn->name;
                    }

                    $s->AddFulltextWhere($columns, $search['text_search_type'], $search['text_search']);
                }

                for( $i = 0; $i < count($search['search_field']); $i++ )
                {
                    $s->AddWhere($search['search_field'][$i], $search['search_operator'][$i], $search['search_term'][$i], $search['search_connector'][$i], true);
                }
                break;

            case self::$TYPE_SELECTED:
                $s->AddWhere("$table.$primary_key", SQL::IN, $search['search_term']);
                break;
        }

        switch($query)
        {
            case 'amount':
                return $DB->QueryCount($s->Generate(), $s->Binds(), $primary_key);

            case 'handle':
                return $DB->Query($s->Generate(), $s->Binds());

            case 'ids':
                $ids = array();
                $result = $DB->Query($s->Generate(), $s->Binds());
                while( $row = $DB->NextRow($result) )
                {
                    $ids[] = $row[$primary_key];
                }
                $DB->Free($result);
                return $ids;
        }
    }
}

?>