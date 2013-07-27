<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_YouTube_Extension_Statistics extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'yt';
    protected $_rootElement = 'statistics';

    protected $_videoWatchCount = null;

    protected $_viewCount = null;

    protected $_subscriberCount = null;

    protected $_lastWebAccess = null;

    protected $_favoriteCount = null;

    public function __construct($viewCount = null, $videoWatchCount = null,
        $subscriberCount = null, $lastWebAccess = null,
        $favoriteCount = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_viewCount = $viewCount;
        $this->_videoWatchCount = $videoWatchCount;
        $this->_subscriberCount = $subscriberCount;
        $this->_lastWebAccess = $lastWebAccess;
        $this->_favoriteCount = $favoriteCount;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_videoWatchCount !== null) {
            $element->setAttribute('watchCount', $this->_videoWatchCount);
        }
        if ($this->_viewCount !== null) {
            $element->setAttribute('viewCount', $this->_viewCount);
        }
        if ($this->_subscriberCount !== null) {
            $element->setAttribute('subscriberCount',
                $this->_subscriberCount);
        }
        if ($this->_lastWebAccess !== null) {
            $element->setAttribute('lastWebAccess',
                $this->_lastWebAccess);
        }
        if ($this->_favoriteCount !== null) {
            $element->setAttribute('favoriteCount',
                $this->_favoriteCount);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'videoWatchCount':
            $this->_videoWatchCount = $attribute->nodeValue;
            break;
        case 'viewCount':
            $this->_viewCount = $attribute->nodeValue;
            break;
        case 'subscriberCount':
            $this->_subscriberCount = $attribute->nodeValue;
            break;
        case 'lastWebAccess':
            $this->_lastWebAccess = $attribute->nodeValue;
            break;
        case 'favoriteCount':
            $this->_favoriteCount = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getViewCount()
    {
        return $this->_viewCount;
    }

    public function setViewCount($value)
    {
        $this->_viewCount = $value;
        return $this;
    }

    public function getVideoWatchCount()
    {
        return $this->_videoWatchCount;
    }

    public function setVideoWatchCount($value)
    {
        $this->_videoWatchCount = $value;
        return $this;
    }

    public function getSubscriberCount()
    {
        return $this->_subscriberCount;
    }

    public function setSubscriberCount($value)
    {
        $this->_subscriberCount = $value;
        return $this;
    }

    public function getLastWebAccess()
    {
        return $this->_lastWebAccess;
    }

    public function setLastWebAccess($value)
    {
        $this->_lastWebAccess = $value;
        return $this;
    }

    public function getFavoriteCount()
    {
        return $this->_favoriteCount;
    }

    public function setFavoriteCount($value)
    {
        $this->_favoriteCount = $value;
        return $this;
    }

    public function __toString()
    {
        return 'View Count=' . $this->_viewCount .
            ' VideoWatchCount=' . $this->_videoWatchCount .
            ' SubscriberCount=' . $this->_subscriberCount .
            ' LastWebAccess=' . $this->_lastWebAccess .
            ' FavoriteCount=' . $this->_favoriteCount;
    }

}
