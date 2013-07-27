<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_YouTube_Extension_Duration extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'yt';
    protected $_rootElement = 'duration';
    protected $_seconds = null;

    public function __construct($seconds = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_seconds = $seconds;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_seconds !== null) {
            $element->setAttribute('seconds', $this->_seconds);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'seconds':
            $this->_seconds = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getSeconds()
    {
        return $this->_seconds;
    }

    public function setSeconds($value)
    {
        $this->_seconds = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->_seconds;
    }

}
