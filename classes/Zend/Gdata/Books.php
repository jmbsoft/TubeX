<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/DublinCore.php';

require_once 'Zend/Gdata/Books/CollectionEntry.php';

require_once 'Zend/Gdata/Books/CollectionFeed.php';

require_once 'Zend/Gdata/Books/VolumeEntry.php';

require_once 'Zend/Gdata/Books/VolumeFeed.php';

class Zend_Gdata_Books extends Zend_Gdata
{
    const VOLUME_FEED_URI = 'http://books.google.com/books/feeds/volumes';
    const MY_LIBRARY_FEED_URI = 'http://books.google.com/books/feeds/users/me/collections/library/volumes';
    const MY_ANNOTATION_FEED_URI = 'http://books.google.com/books/feeds/users/me/volumes';
    const AUTH_SERVICE_NAME = 'print';

    public static $namespaces = array(
        array('gbs', 'http://schemas.google.com/books/2008', 1, 0),
        array('dc', 'http://purl.org/dc/terms', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Books');
        $this->registerPackage('Zend_Gdata_Books_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
     }

    public function getVolumeFeed($location = null)
    {
        if ($location == null) {
            $uri = self::VOLUME_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    public function getVolumeEntry($volumeId = null, $location = null)
    {
        if ($volumeId !== null) {
            $uri = self::VOLUME_FEED_URI . "/" . $volumeId;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Books_VolumeEntry');
    }

    public function getUserLibraryFeed($location = null)
    {
        if ($location == null) {
            $uri = self::MY_LIBRARY_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    public function getUserAnnotationFeed($location = null)
    {
        if ($location == null) {
            $uri = self::MY_ANNOTATION_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    public function insertVolume($entry, $location = null)
    {
        if ($location == null) {
            $uri = self::MY_LIBRARY_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::insertEntry(
            $entry, $uri, 'Zend_Gdata_Books_VolumeEntry');
    }

    public function deleteVolume($entry)
    {
        $entry->delete();
    }

}
