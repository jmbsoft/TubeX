<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

require_once 'Zend/Gdata/YouTube/Extension/Description.php';

require_once 'Zend/Gdata/YouTube/Extension/PlaylistTitle.php';

require_once 'Zend/Gdata/YouTube/Extension/PlaylistId.php';

require_once 'Zend/Gdata/Media/Extension/MediaThumbnail.php';

require_once 'Zend/Gdata/YouTube/Extension/Username.php';

require_once 'Zend/Gdata/YouTube/Extension/CountHint.php';

require_once 'Zend/Gdata/YouTube/Extension/QueryString.php';

class Zend_Gdata_YouTube_SubscriptionEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_SubscriptionEntry';

    protected $_feedLink = array();

    protected $_username = null;

    protected $_playlistTitle = null;

    protected $_playlistId = null;

    protected $_mediaThumbnail = null;

    protected $_countHint = null;

    protected $_queryString = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_countHint != null) {
            $element->appendChild($this->_countHint->getDOM($element->ownerDocument));
        }
        if ($this->_playlistTitle != null) {
            $element->appendChild($this->_playlistTitle->getDOM($element->ownerDocument));
        }
        if ($this->_playlistId != null) {
            $element->appendChild($this->_playlistId->getDOM($element->ownerDocument));
        }
        if ($this->_mediaThumbnail != null) {
            $element->appendChild($this->_mediaThumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_username != null) {
            $element->appendChild($this->_username->getDOM($element->ownerDocument));
        }
        if ($this->_queryString != null) {
            $element->appendChild($this->_queryString->getDOM($element->ownerDocument));
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
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        case $this->lookupNamespace('media') . ':' . 'thumbnail':
            $mediaThumbnail = new Zend_Gdata_Media_Extension_MediaThumbnail();
            $mediaThumbnail->transferFromDOM($child);
            $this->_mediaThumbnail = $mediaThumbnail;
            break;
        case $this->lookupNamespace('yt') . ':' . 'countHint':
            $countHint = new Zend_Gdata_YouTube_Extension_CountHint();
            $countHint->transferFromDOM($child);
            $this->_countHint = $countHint;
            break;
        case $this->lookupNamespace('yt') . ':' . 'playlistTitle':
            $playlistTitle = new Zend_Gdata_YouTube_Extension_PlaylistTitle();
            $playlistTitle->transferFromDOM($child);
            $this->_playlistTitle = $playlistTitle;
            break;
        case $this->lookupNamespace('yt') . ':' . 'playlistId':
            $playlistId = new Zend_Gdata_YouTube_Extension_PlaylistId();
            $playlistId->transferFromDOM($child);
            $this->_playlistId = $playlistId;
            break;
        case $this->lookupNamespace('yt') . ':' . 'queryString':
            $queryString = new Zend_Gdata_YouTube_Extension_QueryString();
            $queryString->transferFromDOM($child);
            $this->_queryString = $queryString;
            break;
        case $this->lookupNamespace('yt') . ':' . 'username':
            $username = new Zend_Gdata_YouTube_Extension_Username();
            $username->transferFromDOM($child);
            $this->_username = $username;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
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

    public function getPlaylistId()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getPlaylistId ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_playlistId;
        }
    }

    public function setPlaylistId($id = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_playlistId = $id;
            return $this;
        }
    }

    public function getQueryString()
    {
        return $this->_queryString;
    }

    public function setQueryString($queryString = null)
    {
        $this->_queryString = $queryString;
        return $this;
    }

    public function getPlaylistTitle()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_playlistTitle;
        }
    }

    public function setPlaylistTitle($title = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_playlistTitle = $title;
            return $this;
        }
    }

    public function getCountHint()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getCountHint ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_countHint;
        }
    }

    public function getMediaThumbnail()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getMediaThumbnail ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_mediaThumbnail;
        }
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setUsername($username = null)
    {
        $this->_username = $username;
        return $this;
    }

}
