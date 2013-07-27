<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_App_Extension_Category extends Zend_Gdata_App_Extension
{

    protected $_rootElement = 'category';
    protected $_term = null;
    protected $_scheme = null;
    protected $_label = null;

    public function __construct($term = null, $scheme = null, $label=null)
    {
        parent::__construct();
        $this->_term = $term;
        $this->_scheme = $scheme;
        $this->_label = $label;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_term !== null) {
            $element->setAttribute('term', $this->_term);
        }
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_label !== null) {
            $element->setAttribute('label', $this->_label);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'term':
            $this->_term = $attribute->nodeValue;
            break;
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        case 'label':
            $this->_label = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getTerm()
    {
        return $this->_term;
    }

    public function setTerm($value)
    {
        $this->_term = $value;
        return $this;
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

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($value)
    {
        $this->_label = $value;
        return $this;
    }

}
