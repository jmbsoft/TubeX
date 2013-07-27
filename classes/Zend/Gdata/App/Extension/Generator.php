<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_App_Extension_Generator extends Zend_Gdata_App_Extension
{

    protected $_rootElement = 'generator';
    protected $_uri = null;
    protected $_version = null;

    public function __construct($text = null, $uri = null, $version = null)
    {
        parent::__construct();
        $this->_text = $text;
        $this->_uri = $uri;
        $this->_version = $version;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_uri !== null) {
            $element->setAttribute('uri', $this->_uri);
        }
        if ($this->_version !== null) {
            $element->setAttribute('version', $this->_version);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'uri':
            $this->_uri = $attribute->nodeValue;
            break;
        case 'version':
            $this->_version= $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function setUri($value)
    {
        $this->_uri = $value;
        return $this;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function setVersion($value)
    {
        $this->_version = $value;
        return $this;
    }

}
