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

class Form_Prepare
{

    public static function Standard($table, $location = 'create')
    {
        $schema = GetDBSchema();
        $xtable = $schema->el('//table[name="' . $table . '"]');

        foreach( $xtable->xpath('./columns/column') as $xcolumn )
        {
            $xlocation = $xcolumn->el('./user/' . $location);

            if( empty($xlocation) || !$xlocation->val() )
            {
                if( isset($_REQUEST[$xcolumn->name->val()]) )
                {
                    unset($_REQUEST[$xcolumn->name->val()]);
                }
            }
        }
    }

    public static function Custom($table, $allow_field = 'on_submit')
    {
        $DB = GetDB();

        $result = $DB->Query('SELECT * FROM #', array($table));

        while( $field = $DB->NextRow($result) )
        {
            // User can submit this field
            if( $field[$allow_field] )
            {
                if( $field['type'] == Form_Field::CHECKBOX && !isset($_REQUEST[$field['name']]) )
                {
                    $_REQUEST[$field['name']] = 0;
                }
            }

            // User cannot submit this field
            else
            {
                if( isset($_REQUEST[$field['name']]) )
                {
                    unset($_REQUEST[$field['name']]);
                }
            }
        }

        $DB->Free($result);
    }
}

?>