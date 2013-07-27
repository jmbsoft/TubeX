<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_App_Extension_Link extends Zend_Gdata_App_Extension
{

    protected $_rootElement = 'link';
    protected $_href = null;
    protected $_rel = null;
    protected $_type = null;
    protected $_hrefLang = null;
    protected $_title = null;
    protected $_length = null;

    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null)
    {
        parent::__construct();
        $this->_href = $href;
        $this->_rel = $rel;
        $this->_type = $type;
        $this->_hrefLang = $hrefLang;
        $this->_title = $title;
        $this->_length = $length;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_href !== null) {
            $element->setAttribute('href', $this->_href);
        }
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        if ($this->_hrefLang !== null) {
            $element->setAttribute('hreflang', $this->_hrefLang);
        }
        if ($this->_title !== null) {
            $element->setAttribute('title', $this->_title);
        }
        if ($this->_length !== null) {
            $element->setAttribute('length', $this->_length);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'href':
            $this->_href = $attribute->nodeValue;
            break;
        case 'rel':
            $this->_rel = $attribute->nodeValue;
            break;
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        case 'hreflang':
            $this->_hrefLang = $attribute->nodeValue;
            break;
        case 'title':
            $this->_title = $attribute->nodeValue;
            break;
        case 'length':
            $this->_length = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getHref()
    {
        return $this->_href;
    }

    public function setHref($value)
    {
        $this->_href = $value;
        return $this;
    }

    public function getRel()
    {
        return $this->_rel;
    }

    public function setRel($value)
    {
        $this->_rel = $value;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    public function getHrefLang()
    {
        return $this->_hrefLang;
    }

    public function setHrefLang($value)
    {
        $this->_hrefLang = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($value)
    {
        $this->_title = $value;
        return $this;
    }

    public function getLength()
    {
        return $this->_length;
    }

    public function setLength($value)
    {
        $this->_length = $value;
        return $this;
    }

}
