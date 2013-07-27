<?php


require_once 'Zend/Gdata/App/Extension/Link.php';

require_once 'Zend/Gdata/Calendar/Extension/WebContent.php';

class Zend_Gdata_Calendar_Extension_Link extends Zend_Gdata_App_Extension_Link
{

    protected $_webContent = null;

    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null, $webContent = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($href, $rel, $type, $hrefLang, $title, $length);
        $this->_webContent = $webContent;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_webContent != null) {
            $element->appendChild($this->_webContent->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gCal') . ':' . 'webContent':
            $webContent = new Zend_Gdata_Calendar_Extension_WebContent();
            $webContent->transferFromDOM($child);
            $this->_webContent = $webContent;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getWebContent()
    {
        return $this->_webContent;
    }

    public function setWebContent($value)
    {
        $this->_webContent = $value;
        return $this;
    }


}

