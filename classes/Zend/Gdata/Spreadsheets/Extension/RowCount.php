<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Spreadsheets_Extension_RowCount extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rowCount';
    protected $_rootNamespace = 'gs';

    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $text;
    }

}
