<?php


class Zend_Search_Lucene_FSMAction
{

    private $_object;

    private $_method;

    public function __construct($object, $method)
    {
        $this->_object = $object;
        $this->_method = $method;
    }

    public function doAction()
    {
        $methodName = $this->_method;
        $this->_object->$methodName();
    }
}

