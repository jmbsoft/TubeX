<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/Health/ProfileFeed.php';

require_once 'Zend/Gdata/Health/ProfileListFeed.php';

require_once 'Zend/Gdata/Health/ProfileListEntry.php';

require_once 'Zend/Gdata/Health/ProfileEntry.php';

class Zend_Gdata_Health extends Zend_Gdata
{

    const AUTHSUB_PROFILE_FEED_URI = 
        'https://www.google.com/health/feeds/profile/default';
    const AUTHSUB_REGISTER_FEED_URI = 
        'https://www.google.com/health/feeds/register/default';

    const CLIENTLOGIN_PROFILELIST_FEED_URI = 
        'https://www.google.com/health/feeds/profile/list';
    const CLIENTLOGIN_PROFILE_FEED_URI = 
        'https://www.google.com/health/feeds/profile/ui';
    const CLIENTLOGIN_REGISTER_FEED_URI = 
        'https://www.google.com/health/feeds/register/ui';

    const HEALTH_SERVICE_NAME = 'health';
    const H9_SANDBOX_SERVICE_NAME = 'weaver';

    private $_profileID = null;

    private $_useH9Sandbox = false;

    public static $namespaces =
        array('ccr' => 'urn:astm-org:CCR',
              'batch' => 'http://schemas.google.com/gdata/batch',
              'h9m' => 'http://schemas.google.com/health/metadata',
              'gAcl' => 'http://schemas.google.com/acl/2007',
              'gd' => 'http://schemas.google.com/g/2005');

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0', $useH9Sandbox = false)
    {
        $this->registerPackage('Zend_Gdata_Health');
        $this->registerPackage('Zend_Gdata_Health_Extension_Ccr');
        parent::__construct($client, $applicationId);
        $this->_useH9Sandbox = $useH9Sandbox;
    }

    public function getProfileID()
    {
        return $this->_profileID;
    }

    public function setProfileID($id) {
        $this->_profileID = $id;
        return $this;
    }

    public function getHealthProfileListFeed($query = null)
    {
        if ($this->_httpClient->getClientLoginToken() === null) {
            require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                'Profiles list feed is only available when using ClientLogin');
        }

        if($query === null)  {
            $uri = self::CLIENTLOGIN_PROFILELIST_FEED_URI;
        } else if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else {
            $uri = $query;
        }

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return parent::getFeed($uri, 'Zend_Gdata_Health_ProfileListFeed');
    }

    public function getHealthProfileFeed($query = null)
    {
        if ($this->_httpClient->getClientLoginToken() !== null &&
            $this->getProfileID() == null) {
            require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                'Profile ID must not be null. Did you call setProfileID()?');
        }

        if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else if ($this->_httpClient->getClientLoginToken() !== null &&
                   $query == null) {
            $uri = self::CLIENTLOGIN_PROFILE_FEED_URI . '/' . $this->getProfileID();
        } else if ($query === null) {
            $uri = self::AUTHSUB_PROFILE_FEED_URI;
        } else {
            $uri = $query;
        }

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return parent::getFeed($uri, 'Zend_Gdata_Health_ProfileFeed');
    }

    public function getHealthProfileEntry($query = null)
    {
        if ($query === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Query must not be null');
        } else if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else {
            $uri = $query;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Health_ProfileEntry');
    }

    public function sendHealthNotice($subject, $body, $bodyType = null, $ccrXML = null)
    {
        if ($this->_httpClient->getClientLoginToken()) {
            $profileID = $this->getProfileID();
            if ($profileID !== null) {
                $uri = self::CLIENTLOGIN_REGISTER_FEED_URI . '/' . $profileID;
            } else {
                require_once 'Zend/Gdata/App/AuthException.php';
                throw new Zend_Gdata_App_AuthException(
                    'Profile ID must not be null. Did you call setProfileID()?');
            }
        } else {
            $uri = self::AUTHSUB_REGISTER_FEED_URI;
        }

        $entry = new Zend_Gdata_Health_ProfileEntry();
        $entry->title = $this->newTitle($subject);
        $entry->content = $this->newContent($body);
        $entry->content->type = $bodyType ? $bodyType : 'text';
        $entry->setCcr($ccrXML);

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return $this->insertEntry($entry, $uri, 'Zend_Gdata_Health_ProfileEntry');
    }
}
