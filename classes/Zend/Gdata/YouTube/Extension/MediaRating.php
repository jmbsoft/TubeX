<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_YouTube_Extension_MediaRating extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rating';
    protected $_rootNamespace = 'media';

    protected $_scheme = null;

    protected $_country = null;

    public function __construct($text = null, $scheme = null, $country = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_scheme = $scheme;
        $this->_country = $country;
        $this->_text = $text;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_country != null) {
            $element->setAttribute('country', $this->_country);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        case 'country':
            $this->_country = $attribute->nodeValue;
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

    public function getCountry()
    {
        return $this->_country;
    }

    public function setCountry($value)
    {
        $this->_country = $value;
        return $this;
    }


}
