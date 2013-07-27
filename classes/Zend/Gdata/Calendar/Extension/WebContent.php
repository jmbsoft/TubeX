<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Calendar_Extension_WebContent extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'gCal';
    protected $_rootElement = 'webContent';
    protected $_url = null;
    protected $_height = null;
    protected $_width = null;

    public function __construct($url = null, $height = null, $width = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_height = $height;
        $this->_width = $width;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->url != null) {
            $element->setAttribute('url', $this->_url);
        }
        if ($this->height != null) {
            $element->setAttribute('height', $this->_height);
        }
        if ($this->width != null) {
            $element->setAttribute('width', $this->_width);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
                case 'url':
                        $this->_url = $attribute->nodeValue;
                        break;
                case 'height':
                        $this->_height = $attribute->nodeValue;
                        break;
                case 'width':
                        $this->_width = $attribute->nodeValue;
                        break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getURL()
    {
        return $this->_url;
    }

    public function setURL($value)
    {
        $this->_url = $value;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function setHeight($value)
    {
        $this->_height = $value;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function setWidth($value)
    {
        $this->_width = $value;
        return $this;
    }

}
