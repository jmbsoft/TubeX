<?php


require_once 'Zend/Gdata/Photos.php';

require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Photos/AlbumEntry.php';

class Zend_Gdata_Photos_AlbumFeed extends Zend_Gdata_Feed
{
    protected $_entryClassName = 'Zend_Gdata_Photos_AlbumEntry';
    protected $_feedClassName = 'Zend_Gdata_Photos_AlbumFeed';

    protected $_gphotoId = null;

    protected $_gphotoUser = null;

    protected $_gphotoAccess = null;

    protected $_gphotoLocation = null;

    protected $_gphotoNickname = null;

    protected $_gphotoTimestamp = null;

    protected $_gphotoName = null;

    protected $_gphotoNumPhotos = null;

    protected $_gphotoCommentCount = null;

    protected $_gphotoCommentingEnabled = null;

    protected $_entryKindClassMapping = array(
        'http://schemas.google.com/photos/2007#photo' => 'Zend_Gdata_Photos_PhotoEntry',
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
        if ($this->_gphotoUser != null) {
            $element->appendChild($this->_gphotoUser->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoNickname != null) {
            $element->appendChild($this->_gphotoNickname->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoName != null) {
            $element->appendChild($this->_gphotoName->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoLocation != null) {
            $element->appendChild($this->_gphotoLocation->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoAccess != null) {
            $element->appendChild($this->_gphotoAccess->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoTimestamp != null) {
            $element->appendChild($this->_gphotoTimestamp->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoNumPhotos != null) {
            $element->appendChild($this->_gphotoNumPhotos->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentingEnabled != null) {
            $element->appendChild($this->_gphotoCommentingEnabled->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentCount != null) {
            $element->appendChild($this->_gphotoCommentCount->getDOM($element->ownerDocument));
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
            case $this->lookupNamespace('gphoto') . ':' . 'user';
                $user = new Zend_Gdata_Photos_Extension_User();
                $user->transferFromDOM($child);
                $this->_gphotoUser = $user;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'nickname';
                $nickname = new Zend_Gdata_Photos_Extension_Nickname();
                $nickname->transferFromDOM($child);
                $this->_gphotoNickname = $nickname;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'name';
                $name = new Zend_Gdata_Photos_Extension_Name();
                $name->transferFromDOM($child);
                $this->_gphotoName = $name;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'location';
                $location = new Zend_Gdata_Photos_Extension_Location();
                $location->transferFromDOM($child);
                $this->_gphotoLocation = $location;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'access';
                $access = new Zend_Gdata_Photos_Extension_Access();
                $access->transferFromDOM($child);
                $this->_gphotoAccess = $access;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'timestamp';
                $timestamp = new Zend_Gdata_Photos_Extension_Timestamp();
                $timestamp->transferFromDOM($child);
                $this->_gphotoTimestamp = $timestamp;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'numphotos';
                $numphotos = new Zend_Gdata_Photos_Extension_NumPhotos();
                $numphotos->transferFromDOM($child);
                $this->_gphotoNumPhotos = $numphotos;
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

    public function getGphotoUser()
    {
        return $this->_gphotoUser;
    }

    public function setGphotoUser($value)
    {
        $this->_gphotoUser = $value;
        return $this;
    }

    public function getGphotoAccess()
    {
        return $this->_gphotoAccess;
    }

    public function setGphotoAccess($value)
    {
        $this->_gphotoAccess = $value;
        return $this;
    }

    public function getGphotoLocation()
    {
        return $this->_gphotoLocation;
    }

    public function setGphotoLocation($value)
    {
        $this->_gphotoLocation = $value;
        return $this;
    }

    public function getGphotoName()
    {
        return $this->_gphotoName;
    }

    public function setGphotoName($value)
    {
        $this->_gphotoName = $value;
        return $this;
    }

    public function getGphotoNumPhotos()
    {
        return $this->_gphotoNumPhotos;
    }

    public function setGphotoNumPhotos($value)
    {
        $this->_gphotoNumPhotos = $value;
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

    public function getGphotoId()
    {
        return $this->_gphotoId;
    }

    public function setGphotoId($value)
    {
        $this->_gphotoId = $value;
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

    public function getGphotoNickname()
    {
        return $this->_gphotoNickname;
    }

    public function setGphotoNickname($value)
    {
        $this->_gphotoNickname = $value;
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

}
