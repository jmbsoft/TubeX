<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/App/Extension.php';

require_once 'Zend/Gdata/Extension/Where.php';

require_once 'Zend/Gdata/Extension/When.php';

require_once 'Zend/Gdata/Extension/Who.php';

require_once 'Zend/Gdata/Extension/Recurrence.php';

require_once 'Zend/Gdata/Extension/EventStatus.php';

require_once 'Zend/Gdata/Extension/Comments.php';

require_once 'Zend/Gdata/Extension/Transparency.php';

require_once 'Zend/Gdata/Extension/Visibility.php';

require_once 'Zend/Gdata/Extension/RecurrenceException.php';

require_once 'Zend/Gdata/Extension/ExtendedProperty.php';

require_once 'Zend/Gdata/Extension/OriginalEvent.php';

require_once 'Zend/Gdata/Extension/EntryLink.php';

class Zend_Gdata_Kind_EventEntry extends Zend_Gdata_Entry
{
    protected $_who = array();
    protected $_when = array();
    protected $_where = array();
    protected $_recurrence = null;
    protected $_eventStatus = null;
    protected $_comments = null;
    protected $_transparency = null;
    protected $_visibility = null;
    protected $_recurrenceException = array();
    protected $_extendedProperty = array();
    protected $_originalEvent = null;
    protected $_entryLink = null;

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_who != null) {
            foreach ($this->_who as $who) {
                $element->appendChild($who->getDOM($element->ownerDocument));
            }
        }
        if ($this->_when != null) {
            foreach ($this->_when as $when) {
                $element->appendChild($when->getDOM($element->ownerDocument));
            }
        }
        if ($this->_where != null) {
            foreach ($this->_where as $where) {
                $element->appendChild($where->getDOM($element->ownerDocument));
            }
        }
        if ($this->_recurrenceException != null) {
            foreach ($this->_recurrenceException as $recurrenceException) {
                $element->appendChild($recurrenceException->getDOM($element->ownerDocument));
            }
        }
        if ($this->_extendedProperty != null) {
            foreach ($this->_extendedProperty as $extProp) {
                $element->appendChild($extProp->getDOM($element->ownerDocument));
            }
        }

        if ($this->_recurrence != null) {
            $element->appendChild($this->_recurrence->getDOM($element->ownerDocument));
        }
        if ($this->_eventStatus != null) {
            $element->appendChild($this->_eventStatus->getDOM($element->ownerDocument));
        }
        if ($this->_comments != null) {
            $element->appendChild($this->_comments->getDOM($element->ownerDocument));
        }
        if ($this->_transparency != null) {
            $element->appendChild($this->_transparency->getDOM($element->ownerDocument));
        }
        if ($this->_visibility != null) {
            $element->appendChild($this->_visibility->getDOM($element->ownerDocument));
        }
        if ($this->_originalEvent != null) {
            $element->appendChild($this->_originalEvent->getDOM($element->ownerDocument));
        }
        if ($this->_entryLink != null) {
            $element->appendChild($this->_entryLink->getDOM($element->ownerDocument));
        }


        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gd') . ':' . 'where';
            $where = new Zend_Gdata_Extension_Where();
            $where->transferFromDOM($child);
            $this->_where[] = $where;
            break;
        case $this->lookupNamespace('gd') . ':' . 'when';
            $when = new Zend_Gdata_Extension_When();
            $when->transferFromDOM($child);
            $this->_when[] = $when;
            break;
        case $this->lookupNamespace('gd') . ':' . 'who';
            $who = new Zend_Gdata_Extension_Who();
            $who ->transferFromDOM($child);
            $this->_who[] = $who;
            break;
        case $this->lookupNamespace('gd') . ':' . 'recurrence';
            $recurrence = new Zend_Gdata_Extension_Recurrence();
            $recurrence->transferFromDOM($child);
            $this->_recurrence = $recurrence;
            break;
        case $this->lookupNamespace('gd') . ':' . 'eventStatus';
            $eventStatus = new Zend_Gdata_Extension_EventStatus();
            $eventStatus->transferFromDOM($child);
            $this->_eventStatus = $eventStatus;
            break;
        case $this->lookupNamespace('gd') . ':' . 'comments';
            $comments = new Zend_Gdata_Extension_Comments();
            $comments->transferFromDOM($child);
            $this->_comments = $comments;
            break;
        case $this->lookupNamespace('gd') . ':' . 'transparency';
            $transparency = new Zend_Gdata_Extension_Transparency();
            $transparency ->transferFromDOM($child);
            $this->_transparency = $transparency;
            break;
        case $this->lookupNamespace('gd') . ':' . 'visibility';
            $visiblity = new Zend_Gdata_Extension_Visibility();
            $visiblity ->transferFromDOM($child);
            $this->_visibility = $visiblity;
            break;
        case $this->lookupNamespace('gd') . ':' . 'recurrenceException';
            $recurrenceException = new Zend_Gdata_Extension_RecurrenceException();
            $recurrenceException ->transferFromDOM($child);
            $this->_recurrenceException[] = $recurrenceException;
            break;
        case $this->lookupNamespace('gd') . ':' . 'originalEvent';
            $originalEvent = new Zend_Gdata_Extension_OriginalEvent();
            $originalEvent ->transferFromDOM($child);
            $this->_originalEvent = $originalEvent;
            break;
        case $this->lookupNamespace('gd') . ':' . 'extendedProperty';
            $extProp = new Zend_Gdata_Extension_ExtendedProperty();
            $extProp->transferFromDOM($child);
            $this->_extendedProperty[] = $extProp;
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

    public function getWhen()
    {
        return $this->_when;
    }

    public function setWhen($value)
    {
        $this->_when = $value;
        return $this;
    }

    public function getWhere()
    {
        return $this->_where;
    }

    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }

    public function getWho()
    {
        return $this->_who;
    }

    public function setWho($value)
    {
        $this->_who = $value;
        return $this;
    }

    public function getRecurrence()
    {
        return $this->_recurrence;
    }

    public function setRecurrence($value)
    {
        $this->_recurrence = $value;
        return $this;
    }

    public function getEventStatus()
    {
        return $this->_eventStatus;
    }

    public function setEventStatus($value)
    {
        $this->_eventStatus = $value;
        return $this;
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function setComments($value)
    {
        $this->_comments = $value;
        return $this;
    }

    public function getTransparency()
    {
        return $this->_transparency;
    }

    public function setTransparency($value)
    {
        $this->_transparency = $value;
        return $this;
    }

    public function getVisibility()
    {
        return $this->_visibility;
    }

    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }

    public function getRecurrenceExcption()
    {
        return $this->_recurrenceException;
    }

    public function setRecurrenceException($value)
    {
        $this->_recurrenceException = $value;
        return $this;
    }

    public function getExtendedProperty()
    {
        return $this->_extendedProperty;
    }

    public function setExtendedProperty($value)
    {
        $this->_extendedProperty = $value;
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
