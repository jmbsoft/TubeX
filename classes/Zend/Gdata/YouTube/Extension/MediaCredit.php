<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_YouTube_Extension_MediaCredit extends Zend_Gdata_Extension
{

    protected $_rootElement = 'credit';
    protected $_rootNamespace = 'media';

    protected $_role = null;

    protected $_scheme = null;

    protected $_yttype = null;

    public function __construct($text = null, $role = null,  $scheme = null,
        $yttype = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_role = $role;
        $this->_scheme = $scheme;
        $this->_yttype = $yttype;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_role !== null) {
            $element->setAttribute('role', $this->_role);
        }
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_yttype !== null) {
            $element->setAttributeNS('http://gdata.youtube.com/schemas/2007',
                'yt:type', $this->_yttype);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'role':
                $this->_role = $attribute->nodeValue;
                break;
            case 'scheme':
                $this->_scheme = $attribute->nodeValue;
                break;
            case 'type':
                $this->_yttype = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getRole()
    {
        return $this->_role;
    }

    public function setRole($value)
    {
        $this->_role = $value;
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

    public function getYTtype()
    {
        return $this->_yttype;
    }

    public function setYTtype($value)
    {
        $this->_yttype = $value;
        return $this;
    }

}