<?php


require_once 'Zend/Gdata/Media/Extension/MediaContent.php';

class Zend_Gdata_YouTube_Extension_MediaContent extends Zend_Gdata_Media_Extension_MediaContent
{
    protected $_rootElement = 'content';
    protected $_rootNamespace = 'media';

    protected $_format = null;


    function __construct() {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_format!= null) {
            $element->setAttributeNS($this->lookupNamespace('yt'), 'yt:format', $this->_format);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        $absoluteAttrName = $attribute->namespaceURI . ':' . $attribute->localName;
        if ($absoluteAttrName == $this->lookupNamespace('yt') . ':' . 'format') {
            $this->_format = $attribute->nodeValue;
        } else {
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getFormat()
    {
        return $this->_format;
    }

    public function setFormat($value)
    {
        $this->_format = $value;
        return $this;
    }

}
