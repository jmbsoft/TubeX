<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Calendar_Extension_SendEventNotifications extends Zend_Gdata_Extension
{
    protected $_rootNamespace = 'gCal';
    protected $_rootElement = 'sendEventNotifications';
    protected $_value = null;

    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct();
        $this->_value = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_value !== null) {
            $element->setAttribute('value', ($this->_value ? "true" : "false"));
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'value':
            if ($attribute->nodeValue == "true") {
                $this->_value = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_value = false;
            }
            else {
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for gCal:selected#value.");
            }
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

