<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_YouTube_Extension_Position extends Zend_Gdata_Extension
{

    protected $_rootElement = 'position';
    protected $_rootNamespace = 'yt';

    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_text = $value;
    }

    public function getValue()
    {
        return $this->_text;
    }

    public function setValue($value)
    {
        $this->_text = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getValue();
    }

}

