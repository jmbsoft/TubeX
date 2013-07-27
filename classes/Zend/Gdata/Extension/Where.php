<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Extension/EntryLink.php';

class Zend_Gdata_Extension_Where extends Zend_Gdata_Extension
{

    protected $_rootElement = 'where';
    protected $_label = null;
    protected $_rel = null;
    protected $_valueString = null;
    protected $_entryLink = null;

    public function __construct($valueString = null, $label = null, $rel = null, $entryLink = null)
    {
        parent::__construct();
        $this->_valueString = $valueString;
        $this->_label = $label;
        $this->_rel = $rel;
        $this->_entryLink = $entryLink;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_label !== null) {
            $element->setAttribute('label', $this->_label);
        }
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_valueString !== null) {
            $element->setAttribute('valueString', $this->_valueString);
        }
        if ($this->entryLink !== null) {
            $element->appendChild($this->_entryLink->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'label':
            $this->_label = $attribute->nodeValue;
            break;
        case 'rel':
            $this->_rel = $attribute->nodeValue;
            break;
        case 'valueString':
            $this->_valueString = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gd') . ':' . 'entryLink':
            $entryLink = new Zend_Gdata_Extension_EntryLink();
            $entryLink->transferFromDOM($child);
            $this->_entryLink = $entryLink;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function __toString()
    {
        if ($this->_valueString != null) {
            return $this->_valueString;
        }
        else {
            return parent::__toString();
        }
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

    public function getRel()
    {
        return $this->_rel;
    }

    public function setRel($value)
    {
        $this->_rel = $value;
        return $this;
    }

    public function getValueString()
    {
        return $this->_valueString;
    }

    public function setValueString($value)
    {
        $this->_valueString = $value;
        return $this;
    }

    public function getEntryLink()
    {
        return $this->_entryLink;
    }

    public function setEntryLink($value)
    {
        $this->_entryLink = $value;
        return $this;
    }

}
