<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Extension_OpenSearchItemsPerPage extends Zend_Gdata_Extension
{

    protected $_rootElement = 'itemsPerPage';
    protected $_rootNamespace = 'openSearch';

    public function __construct($text = null)
    {
        parent::__construct();
        $this->_text = $text;
    }

}
