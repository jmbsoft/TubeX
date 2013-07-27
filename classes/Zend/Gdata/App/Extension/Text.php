<?php


require_once 'Zend/Gdata/App/Extension.php';

abstract class Zend_Gdata_App_Extension_Text extends Zend_Gdata_App_Extension
{

    protected $_rootElement = null;
    protected $_type = 'text';

    public function __construct($text = null, $type = 'text')
    {
        parent::__construct();
        $this->_text = $text;
        $this->_type = $type;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

}
