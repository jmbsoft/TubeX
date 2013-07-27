<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Calendar/Extension/Timezone.php';

class Zend_Gdata_Calendar_EventFeed extends Zend_Gdata_Feed
{

    protected $_timezone = null;

    protected $_entryClassName = 'Zend_Gdata_Calendar_EventEntry';

    protected $_feedClassName = 'Zend_Gdata_Calendar_EventFeed';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }

        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gCal') . ':' . 'timezone';
                $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
                $timezone->transferFromDOM($child);
                $this->_timezone = $timezone;
                break;

            default:
                parent::takeChildFromDOM($child);
                break;
        }
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

}
