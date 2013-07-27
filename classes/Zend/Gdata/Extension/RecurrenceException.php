<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Extension/EntryLink.php';

require_once 'Zend/Gdata/Extension/OriginalEvent.php';

class Zend_Gdata_Extension_RecurrenceException extends Zend_Gdata_Extension
{

    protected $_rootElement = 'recurrenceException';
    protected $_specialized = null;
    protected $_entryLink = null;
    protected $_originalEvent = null;

    public function __construct($specialized = null, $entryLink = null,
            $originalEvent = null)
    {
        parent::__construct();
        $this->_specialized = $specialized;
        $this->_entryLink = $entryLink;
        $this->_originalEvent = $originalEvent;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_specialized !== null) {
            $element->setAttribute('specialized', ($this->_specialized ? "true" : "false"));
        }
        if ($this->_entryLink !== null) {
            $element->appendChild($this->_entryLink->getDOM($element->ownerDocument));
        }
        if ($this->_originalEvent !== null) {
            $element->appendChild($this->_originalEvent->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'specialized':
            if ($attribute->nodeValue == "true") {
                $this->_specialized = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_specialized = false;
            }
            else {
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for gCal:selected#value.");
            }
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
        case $this->lookupNamespace('gd') . ':' . 'originalEvent':
            $originalEvent = new Zend_Gdata_Extension_OriginalEvent();
            $originalEvent->transferFromDOM($child);
            $this->_originalEvent = $originalEvent;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getSpecialized()
    {
        return $this->_specialized;
    }

    public function setSpecialized($value)
    {
        $this->_specialized = $value;
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

    public function getOriginalEvent()
    {
        return $this->_originalEvent;
    }

    public function setOriginalEvent($value)
    {
        $this->_originalEvent = $value;
        return $this;
    }

}

