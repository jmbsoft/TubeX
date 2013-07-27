<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

require_once 'Zend/Gdata/Gapps/Extension/EmailList.php';

class Zend_Gdata_Gapps_EmailListEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gapps_EmailListEntry';

    protected $_emailList = null;

    protected $_feedLink = array();

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_emailList !== null) {
            $element->appendChild($this->_emailList->getDOM($element->ownerDocument));
        }
        foreach ($this->_feedLink as $feedLink) {
            $element->appendChild($feedLink->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('apps') . ':' . 'emailList';
                $emailList = new Zend_Gdata_Gapps_Extension_EmailList();
                $emailList->transferFromDOM($child);
                $this->_emailList = $emailList;
                break;
            case $this->lookupNamespace('gd') . ':' . 'feedLink';
                $feedLink = new Zend_Gdata_Extension_FeedLink();
                $feedLink->transferFromDOM($child);
                $this->_feedLink[] = $feedLink;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getEmailList()
    {
        return $this->_emailList;
    }

    public function setEmailList($value)
    {
        $this->_emailList = $value;
        return $this;
    }

    public function getFeedLink($rel = null)
    {
        if ($rel == null) {
            return $this->_feedLink;
        } else {
            foreach ($this->_feedLink as $feedLink) {
                if ($feedLink->rel == $rel) {
                    return $feedLink;
                }
            }
            return null;
        }
    }

    public function setFeedLink($value)
    {
        $this->_feedLink = $value;
        return $this;
    }

}
