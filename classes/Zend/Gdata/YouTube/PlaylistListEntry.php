<?php


require_once 'Zend/Gdata/YouTube.php';

require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

require_once 'Zend/Gdata/YouTube/Extension/Description.php';

require_once 'Zend/Gdata/YouTube/Extension/PlaylistId.php';

require_once 'Zend/Gdata/YouTube/Extension/CountHint.php';

class Zend_Gdata_YouTube_PlaylistListEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_PlaylistListEntry';

    protected $_feedLink = array();

    protected $_description = null;

    protected $_playlistId = null;

    protected $_countHint = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_description != null) {
            $element->appendChild($this->_description->getDOM($element->ownerDocument));
        }
        if ($this->_countHint != null) {
            $element->appendChild($this->_countHint->getDOM($element->ownerDocument));
        }
        if ($this->_playlistId != null) {
            $element->appendChild($this->_playlistId->getDOM($element->ownerDocument));
        }
        if ($this->_feedLink != null) {
            foreach ($this->_feedLink as $feedLink) {
                $element->appendChild($feedLink->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'description':
            $description = new Zend_Gdata_YouTube_Extension_Description();
            $description->transferFromDOM($child);
            $this->_description = $description;
            break;
        case $this->lookupNamespace('yt') . ':' . 'countHint':
            $countHint = new Zend_Gdata_YouTube_Extension_CountHint();
            $countHint->transferFromDOM($child);
            $this->_countHint = $countHint;
            break;
        case $this->lookupNamespace('yt') . ':' . 'playlistId':
            $playlistId = new Zend_Gdata_YouTube_Extension_PlaylistId();
            $playlistId->transferFromDOM($child);
            $this->_playlistId = $playlistId;
            break;
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function setDescription($description = null)
    {
        if ($this->getMajorProtocolVersion() >= 2) {
            $this->setSummary($description);
        } else {
            $this->_description = $description;
        }
        return $this;
    }

    public function getDescription()
    {
        if ($this->getMajorProtocolVersion() >= 2) {
            return $this->getSummary();
        } else {
            return $this->_description;
        }
    }

    public function getCountHint()
    {
        if (($this->getMajorProtocolVersion() == null) ||
            ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The yt:countHint ' . 
                'element is not supported in versions earlier than 2.');
        } else {
            return $this->_countHint;
        }
    }

    public function getPlaylistId()
    {
        if (($this->getMajorProtocolVersion() == null) ||
            ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The yt:playlistId ' . 
                'element is not supported in versions earlier than 2.');
        } else {
            return $this->_playlistId;
        }
    }

    public function setFeedLink($feedLink = null)
    {
        $this->_feedLink = $feedLink;
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

    public function getPlaylistVideoFeedUrl()
    {
        if ($this->getMajorProtocolVersion() >= 2) {
            return $this->getContent()->getSrc();
        } else {
            return $this->getFeedLink(Zend_Gdata_YouTube::PLAYLIST_REL)->href;
        }
    }

}
