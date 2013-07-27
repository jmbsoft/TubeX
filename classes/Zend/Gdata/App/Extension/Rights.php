<?php


require_once 'Zend/Gdata/App/Extension/Text.php';

class Zend_Gdata_App_Extension_Rights extends Zend_Gdata_App_Extension_Text
{

    protected $_rootElement = 'rights';

    public function __construct($text = null)
    {
        parent::__construct();
        $this->_text = $text;
    }

}
