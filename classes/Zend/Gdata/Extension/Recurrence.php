<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Extension_Recurrence extends Zend_Gdata_Extension
{

    protected $_rootElement = 'recurrence';

    public function __construct($text = null)
    {
        parent::__construct();
        $this->_text = $text;
    }

}
