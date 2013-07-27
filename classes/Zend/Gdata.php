<?php


require_once 'Zend/Gdata/App.php';

class Zend_Gdata extends Zend_Gdata_App
{

    const AUTH_SERVICE_NAME = 'xapi';

    protected $_defaultPostUri = null;

    protected $_registeredPackages = array(
            'Zend_Gdata_Kind',
            'Zend_Gdata_Extension',
            'Zend_Gdata',
            'Zend_Gdata_App_Extension',
            'Zend_Gdata_App');

    public static $namespaces = array(
        array('gd', 'http://schemas.google.com/g/2005', 1, 0),
        array('openSearch', 'http://a9.com/-/spec/opensearchrss/1.0/', 1, 0),
        array('openSearch', 'http://a9.com/-/spec/opensearch/1.1/', 2, 0),
        array('rss', 'http://blogs.law.harvard.edu/tech/rss', 1, 0)
    );

    protected $_httpClient;

    protected static $_staticHttpClient = null;

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        parent::__construct($client, $applicationId);
    }

    public static function import($uri, $client = null,
        $className='Zend_Gdata_Feed')
    {
        $app = new Zend_Gdata($client);
        $requestData = $app->decodeRequest('GET', $uri);
        $response = $app->performHttpRequest($requestData['method'], $requestData['url']);

        $feedContent = $response->getBody();

        $feed = self::importString($feedContent, $className);
        if ($client != null) {
            $feed->setHttpClient($client);
        }
        return $feed;
    }

    public function getFeed($location, $className='Zend_Gdata_Feed')
    {
        if (is_string($location)) {
            $uri = $location;
        } elseif ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You must specify the location as either a string URI ' .
                    'or a child of Zend_Gdata_Query');
        }
        return parent::getFeed($uri, $className);
    }

    public function getEntry($location, $className='Zend_Gdata_Entry')
    {
        if (is_string($location)) {
            $uri = $location;
        } elseif ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You must specify the location as either a string URI ' .
                    'or a child of Zend_Gdata_Query');
        }
        return parent::getEntry($uri, $className);
    }

    public function performHttpRequest($method, $url, $headers = array(), $body = null, $contentType = null, $remainingRedirects = null)
    {
        if ($this->_httpClient instanceof Zend_Gdata_HttpClient) {
            $filterResult = $this->_httpClient->filterHttpRequest($method, $url, $headers, $body, $contentType);
            $method = $filterResult['method'];
            $url = $filterResult['url'];
            $body = $filterResult['body'];
            $headers = $filterResult['headers'];
            $contentType = $filterResult['contentType'];
            return $this->_httpClient->filterHttpResponse(parent::performHttpRequest($method, $url, $headers, $body, $contentType, $remainingRedirects));
        } else {
            return parent::performHttpRequest($method, $url, $headers, $body, $contentType, $remainingRedirects);
        }
    }

    public function isAuthenticated()
    {
        $client = parent::getHttpClient();
        if ($client->getClientLoginToken() ||
            $client->getAuthSubToken()) {
                return true;
        }

        return false;
    }

}
