<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaHash extends Zend_Gdata_Extension
{

    protected $_rootElement = 'hash';
    protected $_rootNamespace = 'media';
    protected $_algo = null;

    public function __construct($text = null, $algo = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_algo = $algo;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_algo !== null) {
            $element->setAttribute('algo', $this->_algo);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'algo':
            $this->_algo = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getAlgo()
    {
        return $this->_algo;
    }

    public function setAlgo($value)
    {
        $this->_algo = $value;
        return $this;
    }

}
