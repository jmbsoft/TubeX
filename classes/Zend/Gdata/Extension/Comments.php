<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

class Zend_Gdata_Extension_Comments extends Zend_Gdata_Extension
{

    protected $_rootElement = 'comments';
    protected $_rel = null;
    protected $_feedLink = null;

    public function __construct($rel = null, $feedLink = null)
    {
        parent::__construct();
        $this->_rel = $rel;
        $this->_feedLink = $feedLink;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_rel !== null) {
            $element->setAttribute('rel', $this->_rel);
        }
        if ($this->_feedLink !== null) {
            $element->appendChild($this->_feedLink->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'feedLink';
                $feedLink = new Zend_Gdata_Extension_FeedLink();
                $feedLink->transferFromDOM($child);
                $this->_feedLink = $feedLink;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'rel':
            $this->_rel = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
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

    public function getFeedLink()
    {
        return $this->_feedLink;
    }

    public function setFeedLink($value)
    {
        $this->_feedLink = $value;
        return $this;
    }

}
