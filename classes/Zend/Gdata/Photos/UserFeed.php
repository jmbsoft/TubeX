<?php


require_once 'Zend/Gdata/Photos.php';

require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Photos/UserEntry.php';

require_once 'Zend/Gdata/Photos/AlbumEntry.php';

require_once 'Zend/Gdata/Photos/PhotoEntry.php';

require_once 'Zend/Gdata/Photos/TagEntry.php';

require_once 'Zend/Gdata/Photos/CommentEntry.php';

class Zend_Gdata_Photos_UserFeed extends Zend_Gdata_Feed
{

    protected $_gphotoUser = null;

    protected $_gphotoThumbnail = null;

    protected $_gphotoNickname = null;

    protected $_entryClassName = 'Zend_Gdata_Photos_UserEntry';
    protected $_feedClassName = 'Zend_Gdata_Photos_UserFeed';

    protected $_entryKindClassMapping = array(
        'http://schemas.google.com/photos/2007#album' => 'Zend_Gdata_Photos_AlbumEntry',
        'http://schemas.google.com/photos/2007#photo' => 'Zend_Gdata_Photos_PhotoEntry',
        'http://schemas.google.com/photos/2007#comment' => 'Zend_Gdata_Photos_CommentEntry',
        'http://schemas.google.com/photos/2007#tag' => 'Zend_Gdata_Photos_TagEntry'
    );

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
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
            case $this->lookupNamespace('gphoto') . ':' . 'thumbnail';
                $thumbnail = new Zend_Gdata_Photos_Extension_Thumbnail();
                $thumbnail->transferFromDOM($child);
                $this->_gphotoThumbnail = $thumbnail;
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

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoUser != null) {
            $element->appendChild($this->_gphotoUser->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoNickname != null) {
            $element->appendChild($this->_gphotoNickname->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoThumbnail != null) {
            $element->appendChild($this->_gphotoThumbnail->getDOM($element->ownerDocument));
        }

        return $element;
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

}
