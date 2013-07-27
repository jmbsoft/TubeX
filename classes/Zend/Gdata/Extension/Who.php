<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Extension/AttendeeStatus.php';

require_once 'Zend/Gdata/Extension/AttendeeType.php';

require_once 'Zend/Gdata/Extension/EntryLink.php';

class Zend_Gdata_Extension_Who extends Zend_Gdata_Extension
{

    protected $_rootElement = 'who';
    protected $_email = null;
    protected $_rel = null;
    protected $_valueString = null;
    protected $_attendeeStatus = null;
    protected $_attendeeType = null;
    protected $_entryLink = null;

    public function __construct($email = null, $rel = null, $valueString = null,
        $attendeeStatus = null, $attendeeType = null, $entryLink = null)
    {
        parent::__construct();
        $this->_email = $email;
        $this->_rel = $rel;
        $this->_valueString = $valueString;
        $this->_attendeeStatus = $attendeeStatus;
        $this->_attendeeType = $attendeeType;
        $this->_entryLink = $entryLink;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_email !== null) {
            $element->setAttribute('email', $this->_email);
        }
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_valueString !== null) {
            $element->setAttribute('valueString', $this->_valueString);
        }
        if ($this->_attendeeStatus !== null) {
            $element->appendChild($this->_attendeeStatus->getDOM($element->ownerDocument));
        }
        if ($this->_attendeeType !== null) {
            $element->appendChild($this->_attendeeType->getDOM($element->ownerDocument));
        }
        if ($this->_entryLink !== null) {
            $element->appendChild($this->_entryLink->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'email':
            $this->_email = $attribute->nodeValue;
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
        case $this->lookupNamespace('gd') . ':' . 'attendeeStatus':
            $attendeeStatus = new Zend_Gdata_Extension_AttendeeStatus();
            $attendeeStatus->transferFromDOM($child);
            $this->_attendeeStatus = $attendeeStatus;
            break;
        case $this->lookupNamespace('gd') . ':' . 'attendeeType':
            $attendeeType = new Zend_Gdata_Extension_AttendeeType();
            $attendeeType->transferFromDOM($child);
            $this->_attendeeType = $attendeeType;
            break;
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

    public function getValueString()
    {
        return $this->_valueString;
    }

    public function setValueString($value)
    {
        $this->_valueString = $value;
        return $this;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function setEmail($value)
    {
        $this->_email = $value;
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

    public function getAttendeeStatus()
    {
        return $this->_attendeeStatus;
    }

    public function setAttendeeStatus($value)
    {
        $this->_attendeeStatus = $value;
        return $this;
    }

    public function getAttendeeType()
    {
        return $this->_attendeeType;
    }

    public function setAttendeeType($value)
    {
        $this->_attendeeType = $value;
        return $this;
    }

}
