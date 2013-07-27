<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Gapps.php';

require_once 'Zend/Gdata/Photos/Extension/Nickname.php';

require_once 'Zend/Gdata/Photos/Extension/Thumbnail.php';

require_once 'Zend/Gdata/Photos/Extension/QuotaCurrent.php';

require_once 'Zend/Gdata/Photos/Extension/QuotaLimit.php';

require_once 'Zend/Gdata/Photos/Extension/MaxPhotosPerAlbum.php';

require_once 'Zend/Gdata/Photos/Extension/User.php';

require_once 'Zend/Gdata/App/Extension/Category.php';

class Zend_Gdata_Photos_UserEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_UserEntry';

    protected $_gphotoNickname = null;

    protected $_gphotoUser = null;

    protected $_gphotoThumbnail = null;

    protected $_gphotoQuotaLimit = null;

    protected $_gphotoQuotaCurrent = null;

    protected $_gphotoMaxPhotosPerAlbum = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#user',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoNickname !== null) {
            $element->appendChild($this->_gphotoNickname->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoThumbnail !== null) {
            $element->appendChild($this->_gphotoThumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoUser !== null) {
            $element->appendChild($this->_gphotoUser->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoQuotaCurrent !== null) {
            $element->appendChild($this->_gphotoQuotaCurrent->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoQuotaLimit !== null) {
            $element->appendChild($this->_gphotoQuotaLimit->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoMaxPhotosPerAlbum !== null) {
            $element->appendChild($this->_gphotoMaxPhotosPerAlbum->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'nickname';
                $nickname = new Zend_Gdata_Photos_Extension_Nickname();
                $nickname->transferFromDOM($child);
                $this->_gphotoNickname = $nickname;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'thumbnail';
                $thumbnail = new Zend_Gdata_Photos_Extension_Thumbnail();
                $thumbnail->transferFromDOM($child);
                $this->_gphotoThumbnail = $thumbnail;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'user';
                $user = new Zend_Gdata_Photos_Extension_User();
                $user->transferFromDOM($child);
                $this->_gphotoUser = $user;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'quotacurrent';
                $quotaCurrent = new Zend_Gdata_Photos_Extension_QuotaCurrent();
                $quotaCurrent->transferFromDOM($child);
                $this->_gphotoQuotaCurrent = $quotaCurrent;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'quotalimit';
                $quotaLimit = new Zend_Gdata_Photos_Extension_QuotaLimit();
                $quotaLimit->transferFromDOM($child);
                $this->_gphotoQuotaLimit = $quotaLimit;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'maxPhotosPerAlbum';
                $maxPhotosPerAlbum = new Zend_Gdata_Photos_Extension_MaxPhotosPerAlbum();
                $maxPhotosPerAlbum->transferFromDOM($child);
                $this->_gphotoMaxPhotosPerAlbum = $maxPhotosPerAlbum;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
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

    public function getGphotoThumbnail()
    {
        return $this->_gphotoThumbnail;
    }

    public function setGphotoThumbnail($value)
    {
        $this->_gphotoThumbnail = $value;
        return $this;
    }

    public function getGphotoQuotaCurrent()
    {
        return $this->_gphotoQuotaCurrent;
    }

    public function setGphotoQuotaCurrent($value)
    {
        $this->_gphotoQuotaCurrent = $value;
        return $this;
    }

    public function getGphotoQuotaLimit()
    {
        return $this->_gphotoQuotaLimit;
    }

    public function setGphotoQuotaLimit($value)
    {
        $this->_gphotoQuotaLimit = $value;
        return $this;
    }

    public function getGphotoMaxPhotosPerAlbum()
    {
        return $this->_gphotoMaxPhotosPerAlbum;
    }

    public function setGphotoMaxPhotosPerAlbum($value)
    {
        $this->_gphotoMaxPhotosPerAlbum = $value;
        return $this;
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

}
