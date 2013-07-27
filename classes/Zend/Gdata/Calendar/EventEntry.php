<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Kind/EventEntry.php';

require_once 'Zend/Gdata/Calendar/Extension/SendEventNotifications.php';

require_once 'Zend/Gdata/Calendar/Extension/Timezone.php';

require_once 'Zend/Gdata/Calendar/Extension/Link.php';

require_once 'Zend/Gdata/Calendar/Extension/QuickAdd.php';

class Zend_Gdata_Calendar_EventEntry extends Zend_Gdata_Kind_EventEntry
{

    protected $_entryClassName = 'Zend_Gdata_Calendar_EventEntry';
    protected $_sendEventNotifications = null;
    protected $_timezone = null;
    protected $_quickadd = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_sendEventNotifications != null) {
            $element->appendChild($this->_sendEventNotifications->getDOM($element->ownerDocument));
        }
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }
        if ($this->_quickadd != null) {
            $element->appendChild($this->_quickadd->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gCal') . ':' . 'sendEventNotifications';
                $sendEventNotifications = new Zend_Gdata_Calendar_Extension_SendEventNotifications();
                $sendEventNotifications->transferFromDOM($child);
                $this->_sendEventNotifications = $sendEventNotifications;
                break;
            case $this->lookupNamespace('gCal') . ':' . 'timezone';
                $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
                $timezone->transferFromDOM($child);
                $this->_timezone = $timezone;
                break;
            case $this->lookupNamespace('atom') . ':' . 'link';
                $link = new Zend_Gdata_Calendar_Extension_Link();
                $link->transferFromDOM($child);
                $this->_link[] = $link;
                break;
            case $this->lookupNamespace('gCal') . ':' . 'quickadd';
                $quickadd = new Zend_Gdata_Calendar_Extension_QuickAdd();
                $quickadd->transferFromDOM($child);
                $this->_quickadd = $quickadd;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getSendEventNotifications()
    {
        return $this->_sendEventNotifications;
    }

    public function setSendEventNotifications($value)
    {
        $this->_sendEventNotifications = $value;
        return $this;
    }

    public function getTimezone()
    {
        return $this->_timezone;
    }

    public function setTimezone($value)
    {
        $this->_timezone = $value;
        return $this;
    }

    public function getQuickAdd()
    {
        return $this->_quickadd;
    }

    public function setQuickAdd($value)
    {
        $this->_quickadd = $value;
        return $this;
    }

}
