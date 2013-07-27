<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Extension_Visibility extends Zend_Gdata_Extension
{

    protected $_rootElement = 'visibility';
    protected $_value = null;

    public function __construct($value = null)
    {
        parent::__construct();
        $this->_value = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_value !== null) {
            $element->setAttribute('value', $this->_value);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'value':
            $this->_value = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getValue();
    }

}

