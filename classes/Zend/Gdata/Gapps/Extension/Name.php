<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Gapps.php';

class Zend_Gdata_Gapps_Extension_Name extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'apps';
    protected $_rootElement = 'name';

    protected $_familyName = null;

    protected $_givenName = null;

    public function __construct($familyName = null, $givenName = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct();
        $this->_familyName = $familyName;
        $this->_givenName = $givenName;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_familyName !== null) {
            $element->setAttribute('familyName', $this->_familyName);
        }
        if ($this->_givenName !== null) {
            $element->setAttribute('givenName', $this->_givenName);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'familyName':
            $this->_familyName = $attribute->nodeValue;
            break;
        case 'givenName':
            $this->_givenName = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getFamilyName()
    {
        return $this->_familyName;
    }

    public function setFamilyName($value)
    {
        $this->_familyName = $value;
        return $this;
    }

    public function getGivenName()
    {
        return $this->_givenName;
    }

    public function setGivenName($value)
    {
        $this->_givenName = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getGivenName() . ' ' . $this->getFamilyName();
    }

}
