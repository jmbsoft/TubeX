<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_YouTube_Extension_State extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'yt';
    protected $_rootElement = 'state';
    protected $_name = null;
    protected $_reasonCode = null;
    protected $_helpUrl = null;

    public function __construct($explanation = null, $name = null,
                                $reasonCode = null, $helpUrl = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_text = $explanation;
        $this->_name = $name;
        $this->_reasonCode = $reasonCode;
        $this->_helpUrl = $reasonCode;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_name !== null) {
            $element->setAttribute('name', $this->_name);
        }
        if ($this->_reasonCode !== null) {
            $element->setAttribute('reasonCode', $this->_reasonCode);
        }
        if ($this->_helpUrl !== null) {
            $element->setAttribute('helpUrl', $this->_helpUrl);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'name':
            $this->_name = $attribute->nodeValue;
            break;
        case 'reasonCode':
            $this->_reasonCode = $attribute->nodeValue;
            break;
        case 'helpUrl':
            $this->_helpUrl = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }

    public function getReasonCode()
    {
        return $this->_reasonCode;
    }

    public function setReasonCode($value)
    {
        $this->_reasonCode = $value;
        return $this;
    }

    public function getHelpUrl()
    {
        return $this->_helpUrl;
    }

    public function setHelpUrl($value)
    {
        $this->_helpUrl = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->_text;
    }

}
