<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/Gbase/ItemFeed.php';

require_once 'Zend/Gdata/Gbase/ItemEntry.php';

require_once 'Zend/Gdata/Gbase/SnippetEntry.php';

require_once 'Zend/Gdata/Gbase/SnippetFeed.php';

class Zend_Gdata_Gbase extends Zend_Gdata
{

    const GBASE_ITEM_FEED_URI = 'http://www.google.com/base/feeds/items';

    const GBASE_SNIPPET_FEED_URI = 'http://www.google.com/base/feeds/snippets';

    const AUTH_SERVICE_NAME = 'gbase';

    protected $_defaultPostUri = self::GBASE_ITEM_FEED_URI;

    public static $namespaces = array(
        array('g', 'http://base.google.com/ns/1.0', 1, 0),
        array('batch', 'http://schemas.google.com/gdata/batch', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Gbase');
        $this->registerPackage('Zend_Gdata_Gbase_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    public function getGbaseItemFeed($location = null)
    {
        if ($location === null) {
            $uri = self::GBASE_ITEM_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Gbase_ItemFeed');
    }

    public function getGbaseItemEntry($location = null)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Gbase_ItemEntry');
    }

    public function insertGbaseItem($entry, $dryRun = false)
    {
        if ($dryRun == false) {
            $uri = $this->_defaultPostUri;
        } else {
            $uri = $this->_defaultPostUri . '?dry-run=true';
        }
        $newitem = $this->insertEntry($entry, $uri, 'Zend_Gdata_Gbase_ItemEntry');
        return $newitem;
    }

    public function updateGbaseItem($entry, $dryRun = false)
    {
        $returnedEntry = $entry->save($dryRun);
        return $returnedEntry;
    }

    public function deleteGbaseItem($entry, $dryRun = false)
    {
        $entry->delete($dryRun);
        return $this;
    }

    public function getGbaseSnippetFeed($location = null)
    {
        if ($location === null) {
            $uri = self::GBASE_SNIPPET_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Gbase_SnippetFeed');
    }
}
