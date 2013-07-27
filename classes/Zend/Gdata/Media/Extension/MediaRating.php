<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaRating extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rating';
    protected $_rootNamespace = 'media';

    protected $_scheme = null;

    public function __construct($text = null, $scheme = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_scheme = $scheme;
        $this->_text = $text;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getScheme()
    {
        return $this->_scheme;
    }

    public function setScheme($value)
    {
        $this->_scheme = $value;
        return $this;
    }

}
