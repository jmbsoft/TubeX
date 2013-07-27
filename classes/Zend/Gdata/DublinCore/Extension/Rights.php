<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_DublinCore_Extension_Rights extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'dc';
    protected $_rootElement = 'rights';

    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_DublinCore::$namespaces);
        parent::__construct();
        $this->_text = $value;
    }

}
