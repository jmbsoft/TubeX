<?php


require_once 'Zend/Gdata/Media/Entry.php';

require_once 'Zend/Gdata/Photos/Extension/PhotoId.php';

require_once 'Zend/Gdata/Photos/Extension/Version.php';

require_once 'Zend/Gdata/Photos/Extension/AlbumId.php';

require_once 'Zend/Gdata/Photos/Extension/Id.php';

require_once 'Zend/Gdata/Photos/Extension/Width.php';

require_once 'Zend/Gdata/Photos/Extension/Height.php';

require_once 'Zend/Gdata/Photos/Extension/Size.php';

require_once 'Zend/Gdata/Photos/Extension/Client.php';

require_once 'Zend/Gdata/Photos/Extension/Checksum.php';

require_once 'Zend/Gdata/Photos/Extension/Timestamp.php';

require_once 'Zend/Gdata/Photos/Extension/CommentingEnabled.php';

require_once 'Zend/Gdata/Photos/Extension/CommentCount.php';

require_once 'Zend/Gdata/Exif/Extension/Tags.php';

require_once 'Zend/Gdata/Geo/Extension/GeoRssWhere.php';

require_once 'Zend/Gdata/App/Extension/Category.php';

class Zend_Gdata_Photos_PhotoEntry extends Zend_Gdata_Media_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_PhotoEntry';

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

    protected $_exifTags = null;

    protected $_geoRssWhere = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#photo',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoAlbumId !== null) {
            $element->appendChild($this->_gphotoAlbumId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoId !== null) {
            $element->appendChild($this->_gphotoId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoVersion !== null) {
            $element->appendChild($this->_gphotoVersion->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoWidth !== null) {
            $element->appendChild($this->_gphotoWidth->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoHeight !== null) {
            $element->appendChild($this->_gphotoHeight->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoSize !== null) {
            $element->appendChild($this->_gphotoSize->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoClient !== null) {
            $element->appendChild($this->_gphotoClient->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoChecksum !== null) {
            $element->appendChild($this->_gphotoChecksum->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoTimestamp !== null) {
            $element->appendChild($this->_gphotoTimestamp->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentingEnabled !== null) {
            $element->appendChild($this->_gphotoCommentingEnabled->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentCount !== null) {
            $element->appendChild($this->_gphotoCommentCount->getDOM($element->ownerDocument));
        }
        if ($this->_exifTags !== null) {
            $element->appendChild($this->_exifTags->getDOM($element->ownerDocument));
        }
        if ($this->_geoRssWhere !== null) {
            $element->appendChild($this->_geoRssWhere->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'albumid';
                $albumId = new Zend_Gdata_Photos_Extension_AlbumId();
                $albumId->transferFromDOM($child);
                $this->_gphotoAlbumId = $albumId;
                break;
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
            case $this->lookupNamespace('exif') . ':' . 'tags';
                $exifTags = new Zend_Gdata_Exif_Extension_Tags();
                $exifTags->transferFromDOM($child);
                $this->_exifTags = $exifTags;
                break;
            case $this->lookupNamespace('georss') . ':' . 'where';
                $geoRssWhere = new Zend_Gdata_Geo_Extension_GeoRssWhere();
                $geoRssWhere->transferFromDOM($child);
                $this->_geoRssWhere = $geoRssWhere;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;

        }
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

    public function getExifTags()
    {
        return $this->_exifTags;
    }

    public function setExifTags($value)
    {
        $this->_exifTags = $value;
        return $this;
    }

    public function getGeoRssWhere()
    {
        return $this->_geoRssWhere;
    }

    public function setGeoRssWhere($value)
    {
        $this->_geoRssWhere = $value;
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
