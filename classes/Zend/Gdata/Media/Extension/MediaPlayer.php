<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaPlayer extends Zend_Gdata_Extension
{

    protected $_rootElement = 'player';
    protected $_rootNamespace = 'media';

    protected $_url = null;

    protected $_width = null;

    protected $_height = null;

    public function __construct($url = null, $width = null, $height = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_width = $width;
        $this->_height = $height;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_url !== null) {
            $element->setAttribute('url', $this->_url);
        }
        if ($this->_width !== null) {
            $element->setAttribute('width', $this->_width);
        }
        if ($this->_height !== null) {
            $element->setAttribute('height', $this->_height);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'url':
            $this->_url = $attribute->nodeValue;
            break;
        case 'width':
            $this->_width = $attribute->nodeValue;
            break;
        case 'height':
            $this->_height = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($value)
    {
        $this->_url = $value;
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

    public function getHeight()
    {
        return $this->_height;
    }

    public function setHeight($value)
    {
        $this->_height = $value;
        return $this;
    }

}
