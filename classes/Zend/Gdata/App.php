<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Http/Client.php';

require_once 'Zend/Version.php';

require_once 'Zend/Gdata/App/MediaSource.php';

class Zend_Gdata_App
{

    const DEFAULT_MAJOR_PROTOCOL_VERSION = 1;

    const DEFAULT_MINOR_PROTOCOL_VERSION = null;

    protected $_httpClient;

    protected static $_staticHttpClient = null;

    protected static $_httpMethodOverride = false;

    protected static $_gzipEnabled = false;

    protected static $_verboseExceptionMessages = true;

    protected $_defaultPostUri = null;

    protected $_registeredPackages = array(
            'Zend_Gdata_App_Extension',
            'Zend_Gdata_App');

    protected static $_maxRedirects = 5;

    protected $_majorProtocolVersion;

    protected $_minorProtocolVersion;

    protected $_useObjectMapping = true;

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->setHttpClient($client, $applicationId);
        // Set default protocol version. Subclasses should override this as
        // needed once a given service supports a new version.
        $this->setMajorProtocolVersion(self::DEFAULT_MAJOR_PROTOCOL_VERSION);
        $this->setMinorProtocolVersion(self::DEFAULT_MINOR_PROTOCOL_VERSION);
    }

    public function registerPackage($name)
    {
        array_unshift($this->_registeredPackages, $name);
    }

    public function getFeed($uri, $className='Zend_Gdata_App_Feed')
    {
        return $this->importUrl($uri, $className, null);
    }

    public function getEntry($uri, $className='Zend_Gdata_App_Entry')
    {
        return $this->importUrl($uri, $className, null);
    }

    public function getHttpClient()
    {
        return $this->_httpClient;
    }

    public function setHttpClient($client,
        $applicationId = 'MyCompany-MyApp-1.0')
    {
        if ($client === null) {
            $client = new Zend_Http_Client();
        }
        if (!$client instanceof Zend_Http_Client) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException(
                'Argument is not an instance of Zend_Http_Client.');
        }
        $userAgent = $applicationId . ' Zend_Framework_Gdata/' .
            Zend_Version::VERSION;
        $client->setHeaders('User-Agent', $userAgent);
        $client->setConfig(array(
            'strictredirects' => true
            )
        );
        $this->_httpClient = $client;
        Zend_Gdata::setStaticHttpClient($client);
        return $this;
    }

    public static function setStaticHttpClient(Zend_Http_Client $httpClient)
    {
        self::$_staticHttpClient = $httpClient;
    }

    public static function getStaticHttpClient()
    {
        if (!self::$_staticHttpClient instanceof Zend_Http_Client) {
            $client = new Zend_Http_Client();
            $userAgent = 'Zend_Framework_Gdata/' . Zend_Version::VERSION;
            $client->setHeaders('User-Agent', $userAgent);
            $client->setConfig(array(
                'strictredirects' => true
                )
            );
            self::$_staticHttpClient = $client;
        }
        return self::$_staticHttpClient;
    }

    public static function setHttpMethodOverride($override = true)
    {
        self::$_httpMethodOverride = $override;
    }

    public static function getHttpMethodOverride()
    {
        return self::$_httpMethodOverride;
    }

    public static function setGzipEnabled($enabled = false)
    {
        if ($enabled && !function_exists('gzinflate')) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You cannot enable gzipped responses if the zlib module ' .
                    'is not enabled in your PHP installation.');

        }
        self::$_gzipEnabled = $enabled;
    }

    public static function getGzipEnabled()
    {
        return self::$_gzipEnabled;
    }

    public static function getVerboseExceptionMessages()
    {
        return self::$_verboseExceptionMessages;
    }

    public static function setVerboseExceptionMessages($verbose)
    {
        self::$_verboseExceptionMessages = $verbose;
    }

    public static function setMaxRedirects($maxRedirects)
    {
        self::$_maxRedirects = $maxRedirects;
    }

    public static function getMaxRedirects()
    {
        return self::$_maxRedirects;
    }

    public function setMajorProtocolVersion($value)
    {
        if (!($value >= 1)) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Major protocol version must be >= 1');
        }
        $this->_majorProtocolVersion = $value;
    }

    public function getMajorProtocolVersion()
    {
        return $this->_majorProtocolVersion;
    }

    public function setMinorProtocolVersion($value)
    {
        if (!($value >= 0)) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Minor protocol version must be >= 0');
        }
        $this->_minorProtocolVersion = $value;
    }

    public function getMinorProtocolVersion()
    {
        return $this->_minorProtocolVersion;
    }

    public function prepareRequest($method,
                                   $url = null,
                                   $headers = array(),
                                   $data = null,
                                   $contentTypeOverride = null)
    {
        // As a convenience, if $headers is null, we'll convert it back to
        // an empty array.
        if ($headers === null) {
            $headers = array();
        }

        $rawData = null;
        $finalContentType = null;
        if ($url == null) {
            $url = $this->_defaultPostUri;
        }

        if (is_string($data)) {
            $rawData = $data;
            if ($contentTypeOverride === null) {
                $finalContentType = 'application/atom+xml';
            }
        } elseif ($data instanceof Zend_Gdata_App_MediaEntry) {
            $rawData = $data->encode();
            if ($data->getMediaSource() !== null) {
                $finalContentType = $rawData->getContentType();
                $headers['MIME-version'] = '1.0';
                $headers['Slug'] = $data->getMediaSource()->getSlug();
            } else {
                $finalContentType = 'application/atom+xml';
            }
            if ($method == 'PUT' || $method == 'DELETE') {
                $editLink = $data->getEditLink();
                if ($editLink != null) {
                    $url = $editLink->getHref();
                }
            }
        } elseif ($data instanceof Zend_Gdata_App_Entry) {
            $rawData = $data->saveXML();
            $finalContentType = 'application/atom+xml';
            if ($method == 'PUT' || $method == 'DELETE') {
                $editLink = $data->getEditLink();
                if ($editLink != null) {
                    $url = $editLink->getHref();
                }
            }
        } elseif ($data instanceof Zend_Gdata_App_MediaSource) {
            $rawData = $data->encode();
            if ($data->getSlug() !== null) {
                $headers['Slug'] = $data->getSlug();
            }
            $finalContentType = $data->getContentType();
        }

        if ($method == 'DELETE') {
            $rawData = null;
        }

        // Set an If-Match header if:
        //   - This isn't a DELETE
        //   - If this isn't a GET, the Etag isn't weak
        //   - A similar header (If-Match/If-None-Match) hasn't already been
        //     set.
        if ($method != 'DELETE' && (
                !array_key_exists('If-Match', $headers) &&
                !array_key_exists('If-None-Match', $headers)
                ) ) {
            $allowWeak = $method == 'GET';
            if ($ifMatchHeader = $this->generateIfMatchHeaderData(
                    $data, $allowWeak)) {
                $headers['If-Match'] = $ifMatchHeader;
            }
        }

        if ($method != 'POST' && $method != 'GET' && Zend_Gdata_App::getHttpMethodOverride()) {
            $headers['x-http-method-override'] = $method;
            $method = 'POST';
        } else {
            $headers['x-http-method-override'] = null;
        }

        if ($contentTypeOverride != null) {
            $finalContentType = $contentTypeOverride;
        }

        return array('method' => $method, 'url' => $url,
            'data' => $rawData, 'headers' => $headers,
            'contentType' => $finalContentType);
    }

    public function performHttpRequest($method, $url, $headers = null,
        $body = null, $contentType = null, $remainingRedirects = null)
    {
        require_once 'Zend/Http/Client/Exception.php';
        if ($remainingRedirects === null) {
            $remainingRedirects = self::getMaxRedirects();
        }
        if ($headers === null) {
            $headers = array();
        }
        // Append a Gdata version header if protocol v2 or higher is in use.
        // (Protocol v1 does not use this header.)
        $major = $this->getMajorProtocolVersion();
        $minor = $this->getMinorProtocolVersion();
        if ($major >= 2) {
            $headers['GData-Version'] = $major +
                    (($minor === null) ? '.' + $minor : '');
        }

        // check the overridden method
        if (($method == 'POST' || $method == 'PUT') && $body === null &&
            $headers['x-http-method-override'] != 'DELETE') {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                        'You must specify the data to post as either a ' .
                        'string or a child of Zend_Gdata_App_Entry');
        }
        if ($url === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'You must specify an URI to which to post.');
        }
        $headers['Content-Type'] = $contentType;
        if (Zend_Gdata_App::getGzipEnabled()) {
            // some services require the word 'gzip' to be in the user-agent
            // header in addition to the accept-encoding header
            if (strpos($this->_httpClient->getHeader('User-Agent'),
                'gzip') === false) {
                $headers['User-Agent'] =
                    $this->_httpClient->getHeader('User-Agent') . ' (gzip)';
            }
            $headers['Accept-encoding'] = 'gzip, deflate';
        } else {
            $headers['Accept-encoding'] = 'identity';
        }

        // Make sure the HTTP client object is 'clean' before making a request
        // In addition to standard headers to reset via resetParameters(),
        // also reset the Slug header
        $this->_httpClient->resetParameters();
        $this->_httpClient->setHeaders('Slug', null);

        // Set the params for the new request to be performed
        $this->_httpClient->setHeaders($headers);
        $this->_httpClient->setUri($url);
        $this->_httpClient->setConfig(array('maxredirects' => 0));

        // Set the proper adapter if we are handling a streaming upload
        $usingMimeStream = false;
        $oldHttpAdapter = null;

        if ($body instanceof Zend_Gdata_MediaMimeStream) {
            $usingMimeStream = true;
            $this->_httpClient->setRawDataStream($body, $contentType);
            $oldHttpAdapter = $this->_httpClient->getAdapter();

            if ($oldHttpAdapter instanceof Zend_Http_Client_Adapter_Proxy) {
                require_once 'Zend/Gdata/HttpAdapterStreamingProxy.php';
                $newAdapter = new Zend_Gdata_HttpAdapterStreamingProxy();
            } else {
                require_once 'Zend/Gdata/HttpAdapterStreamingSocket.php';
                $newAdapter = new Zend_Gdata_HttpAdapterStreamingSocket();
            }
            $this->_httpClient->setAdapter($newAdapter);
        } else {
            $this->_httpClient->setRawData($body, $contentType);
        }

        try {
            $response = $this->_httpClient->request($method);
            // reset adapter
            if ($usingMimeStream) {
                $this->_httpClient->setAdapter($oldHttpAdapter);
            }
        } catch (Zend_Http_Client_Exception $e) {
            // reset adapter
            if ($usingMimeStream) {
                $this->_httpClient->setAdapter($oldHttpAdapter);
            }
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        if ($response->isRedirect() && $response->getStatus() != '304') {
            if ($remainingRedirects > 0) {
                $newUrl = $response->getHeader('Location');
                $response = $this->performHttpRequest(
                    $method, $newUrl, $headers, $body,
                    $contentType, $remainingRedirects);
            } else {
                require_once 'Zend/Gdata/App/HttpException.php';
                throw new Zend_Gdata_App_HttpException(
                        'Number of redirects exceeds maximum', null, $response);
            }
        }
        if (!$response->isSuccessful()) {
            require_once 'Zend/Gdata/App/HttpException.php';
            $exceptionMessage = 'Expected response code 200, got ' .
                $response->getStatus();
            if (self::getVerboseExceptionMessages()) {
                $exceptionMessage .= "\n" . $response->getBody();
            }
            $exception = new Zend_Gdata_App_HttpException($exceptionMessage);
            $exception->setResponse($response);
            throw $exception;
        }
        return $response;
    }

    public static function import($uri, $client = null,
        $className='Zend_Gdata_App_Feed')
    {
        $app = new Zend_Gdata_App($client);
        $requestData = $app->prepareRequest('GET', $uri);
        $response = $app->performHttpRequest(
            $requestData['method'], $requestData['url']);

        $feedContent = $response->getBody();
        if (!$this->_useObjectMapping) {
            return $feedContent;
        }
        $feed = self::importString($feedContent, $className);
        if ($client != null) {
            $feed->setHttpClient($client);
        }
        return $feed;
    }

    public function importUrl($url, $className='Zend_Gdata_App_Feed',
        $extraHeaders = array())
    {
        $response = $this->get($url, $extraHeaders);

        $feedContent = $response->getBody();
        if (!$this->_useObjectMapping) {
            return $feedContent;
        }
        
        $protocolVersionStr = $response->getHeader('GData-Version');
        $majorProtocolVersion = null;
        $minorProtocolVersion = null;
        if ($protocolVersionStr !== null) {
            // Extract protocol major and minor version from header
            $delimiterPos = strpos($protocolVersionStr, '.');
            $length = strlen($protocolVersionStr);
            $major = substr($protocolVersionStr, 0, $delimiterPos);
            $minor = substr($protocolVersionStr, $delimiterPos + 1, $length);
            $majorProtocolVersion = $major;
            $minorProtocolVersion = $minor;
        }

        $feed = self::importString($feedContent, $className,
            $majorProtocolVersion, $minorProtocolVersion);
        if ($this->getHttpClient() != null) {
            $feed->setHttpClient($this->getHttpClient());
        }
        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $feed->setEtag($etag);
        }
        return $feed;
    }

    public static function importString($string,
        $className='Zend_Gdata_App_Feed', $majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        // Load the feed as an XML DOMDocument object
        @ini_set('track_errors', 1);
        $doc = new DOMDocument();
        $success = @$doc->loadXML($string);
        @ini_restore('track_errors');

        if (!$success) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "DOMDocument cannot parse XML: $php_errormsg");
        }

        $feed = new $className();
        $feed->setMajorProtocolVersion($majorProtocolVersion);
        $feed->setMinorProtocolVersion($minorProtocolVersion);
        $feed->transferFromXML($string);
        $feed->setHttpClient(self::getstaticHttpClient());
        return $feed;
    }

    public static function importFile($filename,
            $className='Zend_Gdata_App_Feed', $useIncludePath = false)
    {
        @ini_set('track_errors', 1);
        $feed = @file_get_contents($filename, $useIncludePath);
        @ini_restore('track_errors');
        if ($feed === false) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "File could not be loaded: $php_errormsg");
        }
        return self::importString($feed, $className);
    }

    public function get($uri, $extraHeaders = array())
    {
        $requestData = $this->prepareRequest('GET', $uri, $extraHeaders);
        return $this->performHttpRequest(
            $requestData['method'], $requestData['url'],
            $requestData['headers']);
    }

    public function post($data, $uri = null, $remainingRedirects = null,
            $contentType = null, $extraHeaders = null)
    {
        $requestData = $this->prepareRequest(
            'POST', $uri, $extraHeaders, $data, $contentType);
        return $this->performHttpRequest(
                $requestData['method'], $requestData['url'],
                $requestData['headers'], $requestData['data'],
                $requestData['contentType']);
    }

    public function put($data, $uri = null, $remainingRedirects = null,
            $contentType = null, $extraHeaders = null)
    {
        $requestData = $this->prepareRequest(
            'PUT', $uri, $extraHeaders, $data, $contentType);
        return $this->performHttpRequest(
                $requestData['method'], $requestData['url'],
                $requestData['headers'], $requestData['data'],
                $requestData['contentType']);
    }

    public function delete($data, $remainingRedirects = null)
    {
        if (is_string($data)) {
            $requestData = $this->prepareRequest('DELETE', $data);
        } else {
            $headers = array();

            $requestData = $this->prepareRequest(
                'DELETE', null, $headers, $data);
        }
        return $this->performHttpRequest($requestData['method'],
                                         $requestData['url'],
                                         $requestData['headers'],
                                         '',
                                         $requestData['contentType'],
                                         $remainingRedirects);
    }

    public function insertEntry($data, $uri, $className='Zend_Gdata_App_Entry',
        $extraHeaders = array())
    {
        $response = $this->post($data, $uri, null, null, $extraHeaders);

        $returnEntry = new $className($response->getBody());
        $returnEntry->setHttpClient(self::getstaticHttpClient());

        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $returnEntry->setEtag($etag);
        }

        return $returnEntry;
    }

    public function updateEntry($data, $uri = null, $className = null,
        $extraHeaders = array())
    {
        if ($className === null && $data instanceof Zend_Gdata_App_Entry) {
            $className = get_class($data);
        } elseif ($className === null) {
            $className = 'Zend_Gdata_App_Entry';
        }

        $response = $this->put($data, $uri, null, null, $extraHeaders);
        $returnEntry = new $className($response->getBody());
        $returnEntry->setHttpClient(self::getstaticHttpClient());

        $etag = $response->getHeader('ETag');
        if ($etag !== null) {
            $returnEntry->setEtag($etag);
        }

        return $returnEntry;
    }

    public function __call($method, $args)
    {
        if (preg_match('/^new(\w+)/', $method, $matches)) {
            $class = $matches[1];
            $foundClassName = null;
            foreach ($this->_registeredPackages as $name) {
                 try {
                     @Zend_Loader::loadClass("${name}_${class}");
                     $foundClassName = "${name}_${class}";
                     break;
                 } catch (Zend_Exception $e) {
                     // package wasn't here- continue searching
                 }
            }
            if ($foundClassName != null) {
                $reflectionObj = new ReflectionClass($foundClassName);
                $instance = $reflectionObj->newInstanceArgs($args);
                if ($instance instanceof Zend_Gdata_App_FeedEntryParent) {
                    $instance->setHttpClient($this->_httpClient);

                    // Propogate version data
                    $instance->setMajorProtocolVersion(
                            $this->_majorProtocolVersion);
                    $instance->setMinorProtocolVersion(
                            $this->_minorProtocolVersion);
                }
                return $instance;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                        "Unable to find '${class}' in registered packages");
            }
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("No such method ${method}");
        }
    }

    public function retrieveAllEntriesForFeed($feed) {
        $feedClass = get_class($feed);
        $reflectionObj = new ReflectionClass($feedClass);
        $result = $reflectionObj->newInstance();
        do {
            foreach ($feed as $entry) {
                $result->addEntry($entry);
            }

            $next = $feed->getLink('next');
            if ($next !== null) {
                $feed = $this->getFeed($next->href, $feedClass);
            } else {
                $feed = null;
            }
        }
        while ($feed != null);
        return $result;
    }

    public function enableRequestDebugLogging($logfile,
        $adapter = 'Zend_Gdata_App_LoggingHttpClientAdapterSocket')
    {
        $this->_httpClient->setConfig(array(
            'adapter' => $adapter,
            'logfile' => $logfile
            ));
    }

    public function getNextFeed($feed, $className = null)
    {
        $nextLink = $feed->getNextLink();
        if (!$nextLink) {
            return null;
        }
        $nextLinkHref = $nextLink->getHref();

        if ($className === null) {
            $className = get_class($feed);
        }

        return $this->getFeed($nextLinkHref, $className);
    }

    public function getPreviousFeed($feed, $className = null)
    {
        $previousLink = $feed->getPreviousLink();
        if (!$previousLink) {
            return null;
        }
        $previousLinkHref = $previousLink->getHref();

        if ($className === null) {
            $className = get_class($feed);
        }

        return $this->getFeed($previousLinkHref, $className);
    }

    public function generateIfMatchHeaderData($data, $allowWeek)
    {
        $result = '';
        // Set an If-Match header if an ETag has been set (version >= 2 only)
        if ($this->_majorProtocolVersion >= 2 &&
                $data instanceof Zend_Gdata_App_Entry) {
            $etag = $data->getEtag();
            if (($etag !== null) &&
                    ($allowWeek || substr($etag, 0, 2) != 'W/')) {
                $result = $data->getEtag();
            }
        }
        return $result;
    }

    public function usingObjectMapping()
    {
        return $this->_useObjectMapping;
    }

    public function useObjectMapping($value)
    {
        if ($value === True) {
            $this->_useObjectMapping = true;
        } else {
            $this->_useObjectMapping = false;
        }
    }

}
