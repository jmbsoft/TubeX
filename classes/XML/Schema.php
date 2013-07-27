<?php
#-------------------------------------------------------------------#
# TubeX - Copyright � 2009 JMB Software, Inc. All Rights Reserved.  #
# This file may not be redistributed in whole or significant part.  #
# TubeX IS NOT FREE SOFTWARE                                        #
#-------------------------------------------------------------------#
# http://www.jmbsoft.com/           http://www.jmbsoft.com/license/ #
#-------------------------------------------------------------------#

class XML_Schema
{

    private static $schema_file = 'database.xml';

    public static function AddColumn($table, $name, $label, $validators = array(), $user = array(), $admin = array(), $definition = 'TEXT')
    {
        $schema = GetDBSchema();

        $xcolumns = $schema->el('//table[name="' . $table . '"]/columns');

        // Setup <column>
        $xcolumn = $xcolumns->addChild('column');
        $xcolumn->addChild('name', $name);
        $xcolumn->addChild('label', htmlspecialchars($label));
        $xcolumn->addChild('definition', htmlspecialchars($definition));


        // Setup <admin>
        $xadmin = $xcolumn->addChild('admin');
        $xadmin->addChild('search', isset($admin['search']) ? self::BooleanToString($admin['search']) : 'true');
        $xadmin->addChild('sort', isset($admin['sort']) ? self::BooleanToString($admin['sort']) : 'true');
        $xadmin->addChild('create', isset($admin['create']) ? self::BooleanToString($admin['create']) : 'true');
        $xadmin->addChild('edit', isset($admin['edit']) ? self::BooleanToString($admin['edit']) : 'true');


        // Setup <user>
        if( !empty($user) )
        {
            $xuser = $xcolumn->addChild('user');
            $xuser->addChild('create', self::BooleanToString($user['create']));
            $xuser->addChild('edit', self::BooleanToString($user['edit']));
        }


        // Setup <validator>s
        $reflect = new ReflectionClass('Validator_Type');
        $constants = array_flip($reflect->getConstants());
        foreach( $validators['type'] as $i => $type )
        {
            if( $type != Validator_Type::NONE )
            {
                $xvalidator = $xadmin->addChild('validator');
                $xvalidator->addChild('type', $constants[$type]);
                $xvalidator->addChild('message', htmlspecialchars($validators['message'][$i]));
                $xvalidator->addChild('extras', htmlspecialchars($validators['extras'][$i]));
                $xvalidator->addChild('condition', $type == Validator_Type::NOT_EMPTY ? String::BLANK : Validator::COND_NOT_EMPTY);

                if( isset($xuser) )
                {
                    $xvalidator = $xuser->addChild('validator');
                    $xvalidator->addChild('type', $constants[$type]);
                    $xvalidator->addChild('message', htmlspecialchars($validators['message'][$i]));
                    $xvalidator->addChild('extras', htmlspecialchars($validators['extras'][$i]));
                    $xvalidator->addChild('condition', $type == Validator_Type::NOT_EMPTY ? String::BLANK : Validator::COND_NOT_EMPTY);
                }
            }
        }

        self::WriteXml($schema);
    }

    public static function UpdateColumn($table, $name, $label, $validators = array(), $user = array(), $admin = array(), $definition = 'TEXT')
    {
        $schema = GetDBSchema();

        $xcolumn = $schema->el('//table[name="' . $table . '"]/columns/column[name="' . $name . '"]');
        $xadmin = $xcolumn->el('./admin');
        $xuser = $xcolumn->el('./user');


        // Update <column> values
        $xcolumn->label = htmlspecialchars($label);
        $xcolumn->definition = htmlspecialchars($definition);


        // Update <admin> values
        $xadmin->sort = isset($admin['sort']) ? self::BooleanToString($admin['sort']) : (string)$xadmin->sort;
        $xadmin->search = isset($admin['search']) ? self::BooleanToString($admin['search']) : (string)$xadmin->search;
        $xadmin->create = isset($admin['create']) ? self::BooleanToString($admin['create']) : (string)$xadmin->create;
        $xadmin->edit = isset($admin['edit']) ? self::BooleanToString($admin['edit']) : (string)$xadmin->edit;


        // Update <user> values
        if( isset($xuser) && !empty($xuser) )
        {
            $xuser->create = isset($user['create']) ? self::BooleanToString($user['create']) : (string)$xuser->create;
            $xuser->edit = isset($user['edit']) ? self::BooleanToString($user['edit']) : (string)$xuser->edit;
        }


        // Remove old <validator>s
        unset($xadmin->validator);
        if( isset($xuser) && !empty($xuser) )
        {
            unset($xuser->validator);
        }


        // Setup <validator>s
        $reflect = new ReflectionClass('Validator_Type');
        $constants = array_flip($reflect->getConstants());
        foreach( $validators['type'] as $i => $type )
        {
            if( $type != Validator_Type::NONE )
            {
                $xvalidator = $xadmin->addChild('validator');
                $xvalidator->addChild('type', $constants[$type]);
                $xvalidator->addChild('message', htmlspecialchars($validators['message'][$i]));
                $xvalidator->addChild('extras', htmlspecialchars($validators['extras'][$i]));
                $xvalidator->addChild('condition', $type == Validator_Type::NOT_EMPTY ? String::BLANK : Validator::COND_NOT_EMPTY);

                if( isset($xuser) )
                {
                    $xvalidator = $xuser->addChild('validator');
                    $xvalidator->addChild('type', $constants[$type]);
                    $xvalidator->addChild('message', htmlspecialchars($validators['message'][$i]));
                    $xvalidator->addChild('extras', htmlspecialchars($validators['extras'][$i]));
                    $xvalidator->addChild('condition', $type == Validator_Type::NOT_EMPTY ? String::BLANK : Validator::COND_NOT_EMPTY);
                }
            }
        }

        self::WriteXml($schema);
    }

    public static function DeleteColumn($table, $column)
    {
        $schema = GetDBSchema();

        $xcolumns = $schema->el('//table[name="' . $table . '"]/columns');

        for( $i = 0; $i < count($xcolumns->column); $i++ )
        {
            if( $xcolumns->column[$i]->name == $column )
            {
                unset($xcolumns->column[$i]);
            }
        }

        self::WriteXml($schema);
    }

    public static function WriteXml($schema)
    {
        $xml = new DOMDocument('1.0');
        $xml->preserveWhiteSpace = false;
        $xml->loadXML($schema->asXML());
        $xml->formatOutput = true;
        $src = str_replace("-->\n<!--", "-->\n\n\n\n<!--", $xml->saveXML());
        file_put_contents(INCLUDES_DIR . '/' . self::$schema_file, $src);
    }

    private static function BooleanToString($bool)
    {
        return $bool ? 'true' : 'false';
    }
}

?>