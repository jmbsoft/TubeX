<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Spreadsheets_Extension_ColCount extends Zend_Gdata_Extension
{

    protected $_rootElement = 'colCount';
    protected $_rootNamespace = 'gs';

    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $text;
    }
}
