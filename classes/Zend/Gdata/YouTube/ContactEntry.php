<?php


require_once 'Zend/Gdata/YouTube/UserProfileEntry.php';

require_once 'Zend/Gdata/YouTube/Extension/Status.php';

class Zend_Gdata_YouTube_ContactEntry extends Zend_Gdata_YouTube_UserProfileEntry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_ContactEntry';

    protected $_status = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_status != null) {
            $element->appendChild($this->_status->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'status':
            $status = new Zend_Gdata_YouTube_Extension_Status();
            $status->transferFromDOM($child);
            $this->_status = $status;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function setStatus($status = null)
    {
        $this->_status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }

}
