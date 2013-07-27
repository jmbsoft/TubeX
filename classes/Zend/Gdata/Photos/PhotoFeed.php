<?php


require_once 'Zend/Gdata/Photos.php';

require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Photos/PhotoEntry.php';

class Zend_Gdata_Photos_PhotoFeed extends Zend_Gdata_Feed
{

    protected $_gphotoId = null;

    protected $_gphotoAlbumId = null;

    protected $_gphotoVersion = null;

    protected $_gphotoWidth = null;

    protected $_gphotoHeight = null;

    protected $_gphotoSize = null;

    protected $_gphotoClient = null;

    protected $_gphotoChecksum = null;

    protected $_gphotoTimestamp = null;

    protected $_gphotoCommentCount = null;

    protected $_gphotoCommentingEnabled = null;

    protected $_mediaGroup = null;

    protected $_entryClassName = 'Zend_Gdata_Photos_PhotoEntry';
    protected $_feedClassName = 'Zend_Gdata_Photos_PhotoFeed';

    protected $_entryKindClassMapping = array(
        'http://schemas.google.com/photos/2007#comment' => 'Zend_Gdata_Photos_CommentEntry',
        'http://schemas.google.com/photos/2007#tag' => 'Zend_Gdata_Photos_TagEntry'
    );

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoId != null) {
            $element->appendChild($this->_gphotoId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoVersion != null) {
            $element->appendChild($this->_gphotoVersion->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoWidth != null) {
            $element->appendChild($this->_gphotoWidth->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoHeight != null) {
            $element->appendChild($this->_gphotoHeight->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoSize != null) {
            $element->appendChild($this->_gphotoSize->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoClient != null) {
            $element->appendChild($this->_gphotoClient->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoChecksum != null) {
            $element->appendChild($this->_gphotoChecksum->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoTimestamp != null) {
            $element->appendChild($this->_gphotoTimestamp->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentingEnabled != null) {
            $element->appendChild($this->_gphotoCommentingEnabled->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentCount != null) {
            $element->appendChild($this->_gphotoCommentCount->getDOM($element->ownerDocument));
        }
        if ($this->_mediaGroup != null) {
            $element->appendChild($this->_mediaGroup->getDOM($element->ownerDocument));
        }

        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'id';
                $id = new Zend_Gdata_Photos_Extension_Id();
                $id->transferFromDOM($child);
                $this->_gphotoId = $id;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'version';
                $version = new Zend_Gdata_Photos_Extension_Version();
                $version->transferFromDOM($child);
                $this->_gphotoVersion = $version;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'albumid';
                $albumid = new Zend_Gdata_Photos_Extension_AlbumId();
                $albumid->transferFromDOM($child);
                $this->_gphotoAlbumId = $albumid;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'width';
                $width = new Zend_Gdata_Photos_Extension_Width();
                $width->transferFromDOM($child);
                $this->_gphotoWidth = $width;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'height';
                $height = new Zend_Gdata_Photos_Extension_Height();
                $height->transferFromDOM($child);
                $this->_gphotoHeight = $height;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'size';
                $size = new Zend_Gdata_Photos_Extension_Size();
                $size->transferFromDOM($child);
                $this->_gphotoSize = $size;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'client';
                $client = new Zend_Gdata_Photos_Extension_Client();
                $client->transferFromDOM($child);
                $this->_gphotoClient = $client;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'checksum';
                $checksum = new Zend_Gdata_Photos_Extension_Checksum();
                $checksum->transferFromDOM($child);
                $this->_gphotoChecksum = $checksum;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'timestamp';
                $timestamp = new Zend_Gdata_Photos_Extension_Timestamp();
                $timestamp->transferFromDOM($child);
                $this->_gphotoTimestamp = $timestamp;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'commentingEnabled';
                $commentingEnabled = new Zend_Gdata_Photos_Extension_CommentingEnabled();
                $commentingEnabled->transferFromDOM($child);
                $this->_gphotoCommentingEnabled = $commentingEnabled;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'commentCount';
                $commentCount = new Zend_Gdata_Photos_Extension_CommentCount();
                $commentCount->transferFromDOM($child);
                $this->_gphotoCommentCount = $commentCount;
                break;
            case $this->lookupNamespace('media') . ':' . 'group';
                $mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
                $mediaGroup->transferFromDOM($child);
                $this->_mediaGroup = $mediaGroup;
                break;
            case $this->lookupNamespace('atom') . ':' . 'entry':
                $entryClassName = $this->_entryClassName;
                $tmpEntry = new Zend_Gdata_App_Entry($child);
                $categories = $tmpEntry->getCategory();
                foreach ($categories as $category) {
                    if ($category->scheme == Zend_Gdata_Photos::KIND_PATH &&
                        $this->_entryKindClassMapping[$category->term] != "") {
                            $entryClassName = $this->_entryKindClassMapping[$category->term];
                            break;
                    } else {
                        require_once 'Zend/Gdata/App/Exception.php';
                        throw new Zend_Gdata_App_Exception('Entry is missing kind declaration.');
                    }
                }

                $newEntry = new $entryClassName($child);
                $newEntry->setHttpClient($this->getHttpClient());
                $this->_entry[] = $newEntry;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getGphotoId()
    {
        return $this->_gphotoId;
    }

    public function setGphotoId($value)
    {
        $this->_gphotoId = $value;
        return $this;
    }

    public function getGphotoVersion()
    {
        return $this->_gphotoVersion;
    }

    public function setGphotoVersion($value)
    {
        $this->_gphotoVersion = $value;
        return $this;
    }

    public function getGphotoAlbumId()
    {
        return $this->_gphotoAlbumId;
    }

    public function setGphotoAlbumId($value)
    {
        $this->_gphotoAlbumId = $value;
        return $this;
    }

    public function getGphotoWidth()
    {
        return $this->_gphotoWidth;
    }

    public function setGphotoWidth($value)
    {
        $this->_gphotoWidth = $value;
        return $this;
    }

    public function getGphotoHeight()
    {
        return $this->_gphotoHeight;
    }

    public function setGphotoHeight($value)
    {
        $this->_gphotoHeight = $value;
        return $this;
    }

    public function getGphotoSize()
    {
        return $this->_gphotoSize;
    }

    public function setGphotoSize($value)
    {
        $this->_gphotoSize = $value;
        return $this;
    }

    public function getGphotoClient()
    {
        return $this->_gphotoClient;
    }

    public function setGphotoClient($value)
    {
        $this->_gphotoClient = $value;
        return $this;
    }

    public function getGphotoChecksum()
    {
        return $this->_gphotoChecksum;
    }

    public function setGphotoChecksum($value)
    {
        $this->_gphotoChecksum = $value;
        return $this;
    }

    public function getGphotoTimestamp()
    {
        return $this->_gphotoTimestamp;
    }

    public function setGphotoTimestamp($value)
    {
        $this->_gphotoTimestamp = $value;
        return $this;
    }

    public function getGphotoCommentCount()
    {
        return $this->_gphotoCommentCount;
    }

    public function setGphotoCommentCount($value)
    {
        $this->_gphotoCommentCount = $value;
        return $this;
    }

    public function getGphotoCommentingEnabled()
    {
        return $this->_gphotoCommentingEnabled;
    }

    public function setGphotoCommentingEnabled($value)
    {
        $this->_gphotoCommentingEnabled = $value;
        return $this;
    }

    public function getMediaGroup()
    {
        return $this->_mediaGroup;
    }

    public function setMediaGroup($value)
    {
        $this->_mediaGroup = $value;
        return $this;
    }

}
