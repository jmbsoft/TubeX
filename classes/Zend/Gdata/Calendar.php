<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/Calendar/EventFeed.php';

require_once 'Zend/Gdata/Calendar/EventEntry.php';

require_once 'Zend/Gdata/Calendar/ListFeed.php';

require_once 'Zend/Gdata/Calendar/ListEntry.php';

class Zend_Gdata_Calendar extends Zend_Gdata
{

    const CALENDAR_FEED_URI = 'http://www.google.com/calendar/feeds';
    const CALENDAR_EVENT_FEED_URI = 'http://www.google.com/calendar/feeds/default/private/full';
    const AUTH_SERVICE_NAME = 'cl';

    protected $_defaultPostUri = self::CALENDAR_EVENT_FEED_URI;

    public static $namespaces = array(
        array('gCal', 'http://schemas.google.com/gCal/2005', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Calendar');
        $this->registerPackage('Zend_Gdata_Calendar_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    public function getCalendarEventFeed($location = null)
    {
        if ($location == null) {
            $uri = self::CALENDAR_EVENT_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Calendar_EventFeed');
    }

    public function getCalendarEventEntry($location = null)
    {
        if ($location == null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Calendar_EventEntry');
    }

    public function getCalendarListFeed()
    {
        $uri = self::CALENDAR_FEED_URI . '/default';
        return parent::getFeed($uri,'Zend_Gdata_Calendar_ListFeed');
    }

    public function getCalendarListEntry($location = null)
    {
        if ($location == null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri,'Zend_Gdata_Calendar_ListEntry');
    }

    public function insertEvent($event, $uri=null)
    {
        if ($uri == null) {
            $uri = $this->_defaultPostUri;
        }
        $newEvent = $this->insertEntry($event, $uri, 'Zend_Gdata_Calendar_EventEntry');
        return $newEvent;
    }

}
