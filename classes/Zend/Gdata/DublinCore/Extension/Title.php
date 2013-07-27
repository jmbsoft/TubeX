<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_DublinCore_Extension_Title extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'dc';
    protected $_rootElement = 'title';

    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_DublinCore::$namespaces);
        parent::__construct();
        $this->_text = $value;
    }

}
