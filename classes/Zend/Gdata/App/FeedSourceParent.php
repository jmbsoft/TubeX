<?php


require_once 'Zend/Gdata/App/Entry.php';

require_once 'Zend/Gdata/App/FeedEntryParent.php';

require_once 'Zend/Gdata/App/Extension/Generator.php';

require_once 'Zend/Gdata/App/Extension/Icon.php';

require_once 'Zend/Gdata/App/Extension/Logo.php';

require_once 'Zend/Gdata/App/Extension/Subtitle.php';

abstract class Zend_Gdata_App_FeedSourceParent extends Zend_Gdata_App_FeedEntryParent
{

    protected $_entryClassName = 'Zend_Gdata_App_Entry';

    protected $_rootElement = null;

    protected $_generator = null;
    protected $_icon = null;
    protected $_logo = null;
    protected $_subtitle = null;

    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        parent::setHttpClient($httpClient);
        foreach ($this->_entry as $entry) {
            $entry->setHttpClient($httpClient);
        }
        return $this;
    }

    public function setService($instance)
    {
        parent::setService($instance);
        foreach ($this->_entry as $entry) {
            $entry->setService($instance);
        }
        return $this;
    }

    public function __get($var)
    {
        switch ($var) {
            default:
                return parent::__get($var);
        }
    }


    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_generator != null) {
            $element->appendChild($this->_generator->getDOM($element->ownerDocument));
        }
        if ($this->_icon != null) {
            $element->appendChild($this->_icon->getDOM($element->ownerDocument));
        }
        if ($this->_logo != null) {
            $element->appendChild($this->_logo->getDOM($element->ownerDocument));
        }
        if ($this->_subtitle != null) {
            $element->appendChild($this->_subtitle->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'generator':
            $generator = new Zend_Gdata_App_Extension_Generator();
            $generator->transferFromDOM($child);
            $this->_generator = $generator;
            break;
        case $this->lookupNamespace('atom') . ':' . 'icon':
            $icon = new Zend_Gdata_App_Extension_Icon();
            $icon->transferFromDOM($child);
            $this->_icon = $icon;
            break;
        case $this->lookupNamespace('atom') . ':' . 'logo':
            $logo = new Zend_Gdata_App_Extension_Logo();
            $logo->transferFromDOM($child);
            $this->_logo = $logo;
            break;
        case $this->lookupNamespace('atom') . ':' . 'subtitle':
            $subtitle = new Zend_Gdata_App_Extension_Subtitle();
            $subtitle->transferFromDOM($child);
            $this->_subtitle = $subtitle;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getGenerator()
    {
        return $this->_generator;
    }

    public function setGenerator($value)
    {
        $this->_generator = $value;
        return $this;
    }

    public function getIcon()
    {
        return $this->_icon;
    }

    public function setIcon($value)
    {
        $this->_icon = $value;
        return $this;
    }

    public function getlogo()
    {
        return $this->_logo;
    }

    public function setlogo($value)
    {
        $this->_logo = $value;
        return $this;
    }

    public function getSubtitle()
    {
        return $this->_subtitle;
    }

    public function setSubtitle($value)
    {
        $this->_subtitle = $value;
        return $this;
    }

}
