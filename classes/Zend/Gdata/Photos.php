<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/Photos/UserFeed.php';

require_once 'Zend/Gdata/Photos/AlbumFeed.php';

require_once 'Zend/Gdata/Photos/PhotoFeed.php';

class Zend_Gdata_Photos extends Zend_Gdata
{

    const PICASA_BASE_URI = 'http://picasaweb.google.com/data';
    const PICASA_BASE_FEED_URI = 'http://picasaweb.google.com/data/feed';
    const AUTH_SERVICE_NAME = 'lh2';

    const DEFAULT_PROJECTION = 'api';

    const DEFAULT_VISIBILITY = 'all';

    const DEFAULT_USER = 'default';

    const USER_PATH = 'user';

    const ALBUM_PATH = 'albumid';

    const PHOTO_PATH = 'photoid';

    const COMMUNITY_SEARCH_PATH = 'all';

    const FEED_LINK_PATH = 'http://schemas.google.com/g/2005#feed';

    const KIND_PATH = 'http://schemas.google.com/g/2005#kind';

    public static $namespaces = array(
        array('gphoto', 'http://schemas.google.com/photos/2007', 1, 0),
        array('photo', 'http://www.pheed.com/pheed/', 1, 0),
        array('exif', 'http://schemas.google.com/photos/exif/2007', 1, 0),
        array('georss', 'http://www.georss.org/georss', 1, 0),
        array('gml', 'http://www.opengis.net/gml', 1, 0),
        array('media', 'http://search.yahoo.com/mrss/', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Photos');
        $this->registerPackage('Zend_Gdata_Photos_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    public function getUserFeed($userName = null, $location = null)
    {
        if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('feed');
            if ($userName !== null) {
                $location->setUser($userName);
            }
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($userName !== null) {
                $location->setUser($userName);
            }
            $uri = $location->getQueryUrl();
        } else if ($location !== null) {
            $uri = $location;
        } else if ($userName !== null) {
            $uri = self::PICASA_BASE_FEED_URI . '/' .
                self::DEFAULT_PROJECTION . '/' . self::USER_PATH . '/' .
                $userName;
        } else {
            $uri = self::PICASA_BASE_FEED_URI . '/' .
                self::DEFAULT_PROJECTION . '/' . self::USER_PATH . '/' .
                self::DEFAULT_USER;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Photos_UserFeed');
    }

    public function getAlbumFeed($location = null)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('feed');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Photos_AlbumFeed');
    }

    public function getPhotoFeed($location = null)
    {
        if ($location === null) {
            $uri = self::PICASA_BASE_FEED_URI . '/' .
                self::DEFAULT_PROJECTION . '/' .
                self::COMMUNITY_SEARCH_PATH;
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('feed');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Photos_PhotoFeed');
    }

    public function getUserEntry($location)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('entry');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Photos_UserEntry');
    }

    public function getAlbumEntry($location)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('entry');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Photos_AlbumEntry');
    }

    public function getPhotoEntry($location)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('entry');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Photos_PhotoEntry');
    }

    public function getTagEntry($location)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('entry');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Photos_TagEntry');
    }

    public function getCommentEntry($location)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Photos_UserQuery) {
            $location->setType('entry');
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Photos_CommentEntry');
    }

    public function insertAlbumEntry($album, $uri = null)
    {
        if ($uri === null) {
            $uri = self::PICASA_BASE_FEED_URI . '/' .
                self::DEFAULT_PROJECTION . '/' . self::USER_PATH . '/' .
                self::DEFAULT_USER;
        }
        $newEntry = $this->insertEntry($album, $uri, 'Zend_Gdata_Photos_AlbumEntry');
        return $newEntry;
    }

    public function insertPhotoEntry($photo, $uri = null)
    {
        if ($uri instanceof Zend_Gdata_Photos_AlbumEntry) {
            $uri = $uri->getLink(self::FEED_LINK_PATH)->href;
        }
        if ($uri === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'URI must not be null');
        }
        $newEntry = $this->insertEntry($photo, $uri, 'Zend_Gdata_Photos_PhotoEntry');
        return $newEntry;
    }

    public function insertTagEntry($tag, $uri = null)
    {
        if ($uri instanceof Zend_Gdata_Photos_PhotoEntry) {
            $uri = $uri->getLink(self::FEED_LINK_PATH)->href;
        }
        if ($uri === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'URI must not be null');
        }
        $newEntry = $this->insertEntry($tag, $uri, 'Zend_Gdata_Photos_TagEntry');
        return $newEntry;
    }

    public function insertCommentEntry($comment, $uri = null)
    {
        if ($uri instanceof Zend_Gdata_Photos_PhotoEntry) {
            $uri = $uri->getLink(self::FEED_LINK_PATH)->href;
        }
        if ($uri === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'URI must not be null');
        }
        $newEntry = $this->insertEntry($comment, $uri, 'Zend_Gdata_Photos_CommentEntry');
        return $newEntry;
    }

    public function deleteAlbumEntry($album, $catch)
    {
        if ($catch) {
            try {
                $this->delete($album);
            } catch (Zend_Gdata_App_HttpException $e) {
                if ($e->getResponse()->getStatus() === 409) {
                    $entry = new Zend_Gdata_Photos_AlbumEntry($e->getResponse()->getBody());
                    $this->delete($entry->getLink('edit')->href);
                } else {
                    throw $e;
                }
            }
        } else {
            $this->delete($album);
        }
    }

    public function deletePhotoEntry($photo, $catch)
    {
        if ($catch) {
            try {
                $this->delete($photo);
            } catch (Zend_Gdata_App_HttpException $e) {
                if ($e->getResponse()->getStatus() === 409) {
                    $entry = new Zend_Gdata_Photos_PhotoEntry($e->getResponse()->getBody());
                    $this->delete($entry->getLink('edit')->href);
                } else {
                    throw $e;
                }
            }
        } else {
            $this->delete($photo);
        }
    }

    public function deleteCommentEntry($comment, $catch)
    {
        if ($catch) {
            try {
                $this->delete($comment);
            } catch (Zend_Gdata_App_HttpException $e) {
                if ($e->getResponse()->getStatus() === 409) {
                    $entry = new Zend_Gdata_Photos_CommentEntry($e->getResponse()->getBody());
                    $this->delete($entry->getLink('edit')->href);
                } else {
                    throw $e;
                }
            }
        } else {
            $this->delete($comment);
        }
    }

    public function deleteTagEntry($tag, $catch)
    {
        if ($catch) {
            try {
                $this->delete($tag);
            } catch (Zend_Gdata_App_HttpException $e) {
                if ($e->getResponse()->getStatus() === 409) {
                    $entry = new Zend_Gdata_Photos_TagEntry($e->getResponse()->getBody());
                    $this->delete($entry->getLink('edit')->href);
                } else {
                    throw $e;
                }
            }
        } else {
            $this->delete($tag);
        }
    }

}
