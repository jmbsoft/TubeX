<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_App_Extension_Draft extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'app';
    protected $_rootElement = 'draft';

    public function __construct($text = null)
    {
        parent::__construct();
        $this->_text = $text;
    }

}
