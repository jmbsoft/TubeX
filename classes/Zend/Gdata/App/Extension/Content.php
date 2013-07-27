<?php


require_once 'Zend/Gdata/App/Extension/Text.php';

class Zend_Gdata_App_Extension_Content extends Zend_Gdata_App_Extension_Text
{

    protected $_rootElement = 'content';
    protected $_src = null;

    public function __construct($text = null, $type = 'text', $src = null)
    {
        parent::__construct($text, $type);
        $this->_src = $src;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_src !== null) {
            $element->setAttribute('src', $this->_src);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'src':
            $this->_src = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getSrc()
    {
        return $this->_src;
    }

    public function setSrc($value)
    {
        $this->_src = $value;
        return $this;
    }

}
