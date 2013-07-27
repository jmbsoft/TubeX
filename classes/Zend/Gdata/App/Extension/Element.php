<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_App_Extension_Element extends Zend_Gdata_App_Extension
{

    public function __construct($rootElement=null, $rootNamespace=null, $rootNamespaceURI=null, $text=null){
        parent::__construct();
        $this->_rootElement = $rootElement;
        $this->_rootNamespace = $rootNamespace;
        $this->_rootNamespaceURI = $rootNamespaceURI;
        $this->_text = $text;
    }

    public function transferFromDOM($node)
    {
        parent::transferFromDOM($node);
        $this->_rootNamespace = null;
        $this->_rootNamespaceURI = $node->namespaceURI;
        $this->_rootElement = $node->localName;
    }

}
