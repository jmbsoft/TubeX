<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_App_Extension_Published extends Zend_Gdata_App_Extension
{

    protected $_rootElement = 'published';

    public function __construct($text = null)
    {
        parent::__construct();
        $this->_text = $text;
    }

}
