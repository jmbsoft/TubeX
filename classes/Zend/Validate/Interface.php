<?php


interface Zend_Validate_Interface
{

    public function isValid($value);

    public function getMessages();

    public function getErrors();

}
