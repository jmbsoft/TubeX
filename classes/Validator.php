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

class Validator
{
    // Not empty condition
    const COND_NOT_EMPTY = 'not-empty';

    private $validated = false;

    private $registered;

    private $failed;

    private $set_errors;

    private static $validator = null;

    private function __construct()
    {
        $this->registered = array();
        $this->failed = array();
        $this->set_errors = array();
        $this->validated = false;
    }

    public static function Create()
    {
        if( self::$validator === null )
        {
            self::$validator = new Validator();
        }

        return self::$validator;
    }

    public static function Get()
    {
        return self::Create();
    }

    public function Reset()
    {
        $this->validated = false;
        $this->registered = array();
        $this->failed = array();
        $this->set_errors = array();
    }

    public function Validate($callback = null)
    {
        // Only do the actual validation once
        if( !$this->validated )
        {
            $this->validated = true;

            foreach( $this->registered as $v )
            {
                $result = null;

                switch( $v['type'] )
                {
                    case Validator_Type::CONTAINS:
                        $result = (stristr($v['input'], $v['extras']) !== false);
                        break;

                    case Validator_Type::NOT_CONTAINS:
                        $result = (stristr($v['input'], $v['extras']) === false);
                        break;

                    case Validator_Type::GREATER:
                        $result = ($v['input'] > $v['extras']);
                        break;

                    case Validator_Type::GREATER_EQ:
                        $result = ($v['input'] >= $v['extras']);
                        break;

                    case Validator_Type::IS_ALPHANUM:
                        $result = (preg_match('~^[a-z0-9]+$~i', $v['input']) !== 0);
                        break;

                    case Validator_Type::IS_BETWEEN:
                        $between = explode(',', $v['extras']);
                        $result = ($v['input'] >= $between[0] && $v['input'] <= $between[1]);
                        break;

                    case Validator_Type::IS_EMPTY:
                        $result = ($v['input'] == null || preg_match('~^\s*$~s', $v['input']));
                        break;

                    case Validator_Type::NOT_EMPTY:
                        $result = ($v['input'] !== null && !preg_match('~^\s*$~s', $v['input']));
                        break;

                    case Validator_Type::IS_FALSE:
                        $result = ($v['input'] === false);
                        break;

                    case Validator_Type::NOT_FALSE:
                        $result = ($v['input'] !== false);
                        break;

                    case Validator_Type::IS_NUMERIC:
                        $result = (!is_object($v['input']) && !is_null($v['input']) && !is_bool($v['input']) && !is_resource($v['input']) && preg_match('~^-?[0-9]+$~i', $v['input']) !== 0);
                        break;

                    case Validator_Type::IS_TRUE:
                        $result = ($v['input'] === true);
                        break;

                    case Validator_Type::NOT_TRUE:
                        $result = ($v['input'] !== true);
                        break;

                    case Validator_Type::IS_ZERO:
                        $result = ($v['input'] == 0);
                        break;

                    case Validator_Type::NOT_ZERO:
                        $result = ($v['input'] != 0);
                        break;

                    case Validator_Type::LENGTH_GREATER:
                        $result = (strlen($v['input']) > $v['extras']);
                        break;

                    case Validator_Type::LENGTH_GREATER_EQ:
                        $result = (strlen($v['input']) >= $v['extras']);
                        break;

                    case Validator_Type::LENGTH_LESS:
                        $result = (strlen($v['input']) < $v['extras']);
                        break;

                    case Validator_Type::LENGTH_LESS_EQ:
                        $result = (strlen($v['input']) <= $v['extras']);
                        break;

                    case Validator_Type::LENGTH_BETWEEN:
                        $between = explode(',', $v['extras']);
                        $length = strlen($v['input']);
                        $result = ($length >= $between[0] && $length <= $between[1]);
                        break;

                    case Validator_Type::LESS:
                        $result = ($v['input'] < $v['extras']);
                        break;

                    case Validator_Type::LESS_EQ:
                        $result = ($v['input'] <= $v['extras']);
                        break;

                    case Validator_Type::REGEX_MATCH:
                        $result = (preg_match($v['extras'], $v['input']) !== 0);
                        break;

                    case Validator_Type::REGEX_NO_MATCH:
                        $result = (preg_match($v['extras'], $v['input']) === 0);
                        break;

                    case Validator_Type::VALID_DATE:
                        $result = (preg_match('~^\d\d\d\d-\d\d-\d\d$~', $v['input']) !== 0);
                        break;

                    case Validator_Type::VALID_DATETIME:
                        $result = (preg_match('~^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$~', $v['input']) !== 0);
                        break;

                    case Validator_Type::VALID_EMAIL:
                        $result = (preg_match('~^[\w\d][\w\d\,\.\-]*\@([\w\d\-]+\.)+([a-zA-Z]+)$~i', $v['input']) !== 0);
                        break;

                    case Validator_Type::VALID_HTTP_URL:
                        $result = (preg_match('~^http(s)?://[\w-]+\.[\w-]+(\S+)?$~i', $v['input']) !== 0);
                        break;

                    case Validator_Type::VALID_TIME:
                        $result = (preg_match('~^\d\d:\d\d:\d\d$~', $v['input']) !== 0);
                        break;

                    case Validator_Type::EQUALS:
                        $result = ($v['extras'] == $v['input']);
                        break;

                    case Validator_Type::NOT_EQUALS:
                        $result = ($v['extras'] != $v['input']);
                        break;
                }

                if( $result === false )
                {
                    $this->failed[] = $v['message'];
                }
            }

            // Merge validation errors with manually set error messages
            $this->failed = array_merge($this->failed, $this->set_errors);
        }

        if( $callback != null && function_exists($callback) )
        {
            call_user_func($callback, $this);
        }

        return (count($this->failed) == 0);
    }

    public function Register($input, $vtype, $error_message, $extras = null)
    {
        $this->registered[] = array('input' => $input,
                                    'type' => $vtype,
                                    'message' => $error_message,
                                    'extras' => $extras);
    }

    public function RegisterFromXml($xtable, $section = 'admin', $location = 'create')
    {
        $reflect = new ReflectionClass('Validator_Type');

        foreach( $xtable->xpath('./columns/column') as $xcolumn )
        {
            $xsection = $xcolumn->el('./' . $section);

            if( $section != 'admin' && !empty($xsection) && !$xsection->el('./' . $location)->val() )
            {
                continue;
            }

            $xvalidators = $xcolumn->xpath('./' . $section . '/validator');

            if( empty($xvalidators) )
            {
                continue;
            }

            foreach( $xvalidators as $xvalidator )
            {
                $type = $reflect->getConstant($xvalidator->type->val());
                $value = Request::Get($xcolumn->name->val());

                switch( $xvalidator->condition->val() )
                {
                    case self::COND_NOT_EMPTY:
                        if( String::IsEmpty($value) )
                        {
                            break;
                        }

                    default:
                        $this->Register($value, $type, $xvalidator->message->val(), $xvalidator->extras->val());
                        break;
                }
            }
        }
    }

    public function SetError($message)
    {
        $this->set_errors[] = $message;
    }

    public function GetErrors()
    {
        return ((count($this->failed) > 0) ? $this->failed : null);
    }
}


?>