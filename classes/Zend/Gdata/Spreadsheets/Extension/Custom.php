<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Spreadsheets_Extension_Custom extends Zend_Gdata_Extension
{
    // custom elements have custom names.
    protected $_rootElement = null; // The name of the column
    protected $_rootNamespace = 'gsx';

    public function __construct($column = null, $value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $value;
        $this->_rootElement = $column;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        return $element;
    }

    public function transferFromDOM($node)
    {
        parent::transferFromDOM($node);
        $this->_rootElement = $node->localName;
    }

    public function setColumnName($column)
    {
        $this->_rootElement = $column;
        return $this;
    }

    public function getColumnName()
    {
        return $this->_rootElement;
    }

}
