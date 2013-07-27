<?php


require_once 'Zend/Loader.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Http/Client/Adapter/Interface.php';

require_once 'Zend/Http/Response.php';

class Zend_Http_Client
{

    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const HEAD    = 'HEAD';
    const DELETE  = 'DELETE';
    const TRACE   = 'TRACE';
    const OPTIONS = 'OPTIONS';
    const CONNECT = 'CONNECT';

    const AUTH_BASIC = 'basic';
    //const AUTH_DIGEST = 'digest'; <-- not implemented yet

    const HTTP_1 = '1.1';
    const HTTP_0 = '1.0';

    const CONTENT_TYPE   = 'Content-Type';
    const CONTENT_LENGTH = 'Content-Length';

    const ENC_URLENCODED = 'application/x-www-form-urlencoded';
    const ENC_FORMDATA   = 'multipart/form-data';

    protected $config = array(
        'maxredirects'    => 5,
        'strictredirects' => false,
        'useragent'       => 'Zend_Http_Client',
        'timeout'         => 10,
        'adapter'         => 'Zend_Http_Client_Adapter_Socket',
        'httpversion'     => self::HTTP_1,
        'keepalive'       => false,
        'storeresponse'   => true,
        'strict'          => true
    );

    protected $adapter = null;

    protected $uri;

    protected $headers = array();

    protected $method = self::GET;

    protected $paramsGet = array();

    protected $paramsPost = array();

    protected $enctype = null;

    protected $raw_post_data = null;

    protected $auth;

    protected $files = array();

    protected $cookiejar = null;

    protected $last_request = null;

    protected $last_response = null;

    protected $redirectCounter = 0;

    static protected $_fileInfoDb = null;

    public function __construct($uri = null, $config = null)
    {
        if ($uri !== null) $this->setUri($uri);
        if ($config !== null) $this->setConfig($config);
    }

    public function setUri($uri)
    {
        if (is_string($uri)) {
            $uri = Zend_Uri::factory($uri);
        }

        if (!$uri instanceof Zend_Uri_Http) {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Passed parameter is not a valid HTTP URI.');
        }

        // We have no ports, set the defaults
        if (! $uri->getPort()) {
            $uri->setPort(($uri->getScheme() == 'https' ? 443 : 80));
        }

        $this->uri = $uri;

        return $this;
    }

    public function getUri($as_string = false)
    {
        if ($as_string && $this->uri instanceof Zend_Uri_Http) {
            return $this->uri->__toString();
        } else {
            return $this->uri;
        }
    }

    public function setConfig($config = array())
    {
        if (! is_array($config)) {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Expected array parameter, given ' . gettype($config));
        }

        foreach ($config as $k => $v)
            $this->config[strtolower($k)] = $v;

        // Pass configuration options to the adapter if it exists
        if ($this->adapter instanceof Zend_Http_Client_Adapter_Interface) {
            $this->adapter->setConfig($config);
        }
        
        return $this;
    }

    public function setMethod($method = self::GET)
    {
        $regex = '/^[^\x00-\x1f\x7f-\xff\(\)<>@,;:\\\\"\/\[\]\?={}\s]+$/';
        if (! preg_match($regex, $method)) {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception("'{$method}' is not a valid HTTP request method.");
        }

        if ($method == self::POST && $this->enctype === null)
            $this->setEncType(self::ENC_URLENCODED);

        $this->method = $method;

        return $this;
    }

    public function setHeaders($name, $value = null)
    {
        // If we got an array, go recusive!
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                if (is_string($k)) {
                    $this->setHeaders($k, $v);
                } else {
                    $this->setHeaders($v, null);
                }
            }
        } else {
            // Check if $name needs to be split
            if ($value === null && (strpos($name, ':') > 0))
                list($name, $value) = explode(':', $name, 2);

            // Make sure the name is valid if we are in strict mode
            if ($this->config['strict'] && (! preg_match('/^[a-zA-Z0-9-]+$/', $name))) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("{$name} is not a valid HTTP header name");
            }
            
            $normalized_name = strtolower($name);

            // If $value is null or false, unset the header
            if ($value === null || $value === false) {
                unset($this->headers[$normalized_name]);

            // Else, set the header
            } else {
                // Header names are storred lowercase internally.
                if (is_string($value)) $value = trim($value);
                $this->headers[$normalized_name] = array($name, $value);
            }
        }

        return $this;
    }

    public function getHeader($key)
    {
        $key = strtolower($key);
        if (isset($this->headers[$key])) {
            return $this->headers[$key][1];
        } else {
            return null;
        }
    }

    public function setParameterGet($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $this->_setParameter('GET', $k, $v);
        } else {
            $this->_setParameter('GET', $name, $value);
        }

        return $this;
    }

    public function setParameterPost($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v)
                $this->_setParameter('POST', $k, $v);
        } else {
            $this->_setParameter('POST', $name, $value);
        }

        return $this;
    }

    protected function _setParameter($type, $name, $value)
    {
        $parray = array();
        $type = strtolower($type);
        switch ($type) {
            case 'get':
                $parray = &$this->paramsGet;
                break;
            case 'post':
                $parray = &$this->paramsPost;
                break;
        }

        if ($value === null) {
            if (isset($parray[$name])) unset($parray[$name]);
        } else {
            $parray[$name] = $value;
        }
    }

    public function getRedirectionsCount()
    {
        return $this->redirectCounter;
    }

    public function setAuth($user, $password = '', $type = self::AUTH_BASIC)
    {
        // If we got false or null, disable authentication
        if ($user === false || $user === null) {
            $this->auth = null;

        // Else, set up authentication
        } else {
            // Check we got a proper authentication type
            if (! defined('self::AUTH_' . strtoupper($type))) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Invalid or not supported authentication type: '$type'");
            }

            $this->auth = array(
                'user' => (string) $user,
                'password' => (string) $password,
                'type' => $type
            );
        }

        return $this;
    }

    public function setCookieJar($cookiejar = true)
    {
        if (! class_exists('Zend_Http_CookieJar'))
            require_once 'Zend/Http/CookieJar.php';

        if ($cookiejar instanceof Zend_Http_CookieJar) {
            $this->cookiejar = $cookiejar;
        } elseif ($cookiejar === true) {
            $this->cookiejar = new Zend_Http_CookieJar();
        } elseif (! $cookiejar) {
            $this->cookiejar = null;
        } else {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Invalid parameter type passed as CookieJar');
        }

        return $this;
    }

    public function getCookieJar()
    {
        return $this->cookiejar;
    }

    public function setCookie($cookie, $value = null)
    {
        if (! class_exists('Zend_Http_Cookie'))
            require_once 'Zend/Http/Cookie.php';

        if (is_array($cookie)) {
            foreach ($cookie as $c => $v) {
                if (is_string($c)) {
                    $this->setCookie($c, $v);
                } else {
                    $this->setCookie($v);
                }
            }

            return $this;
        }

        if ($value !== null) $value = urlencode($value);

        if (isset($this->cookiejar)) {
            if ($cookie instanceof Zend_Http_Cookie) {
                $this->cookiejar->addCookie($cookie);
            } elseif (is_string($cookie) && $value !== null) {
                $cookie = Zend_Http_Cookie::fromString("{$cookie}={$value}", $this->uri);
                $this->cookiejar->addCookie($cookie);
            }
        } else {
            if ($cookie instanceof Zend_Http_Cookie) {
                $name = $cookie->getName();
                $value = $cookie->getValue();
                $cookie = $name;
            }

            if (preg_match("/[=,; \t\r\n\013\014]/", $cookie)) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Cookie name cannot contain these characters: =,; \t\r\n\013\014 ({$cookie})");
            }

            $value = addslashes($value);

            if (! isset($this->headers['cookie'])) $this->headers['cookie'] = array('Cookie', '');
            $this->headers['cookie'][1] .= $cookie . '=' . $value . '; ';
        }

        return $this;
    }

    public function setFileUpload($filename, $formname, $data = null, $ctype = null)
    {
        if ($data === null) {
            if (($data = @file_get_contents($filename)) === false) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Unable to read file '{$filename}' for upload");
            }

            if (! $ctype) $ctype = $this->_detectFileMimeType($filename);
        }

        // Force enctype to multipart/form-data
        $this->setEncType(self::ENC_FORMDATA);

        $this->files[$formname] = array(basename($filename), $ctype, $data);

        return $this;
    }

    public function setEncType($enctype = self::ENC_URLENCODED)
    {
        $this->enctype = $enctype;

        return $this;
    }

    public function setRawData($data, $enctype = null)
    {
        $this->raw_post_data = $data;
        $this->setEncType($enctype);

        return $this;
    }

    public function resetParameters()
    {
        // Reset parameter data
        $this->paramsGet     = array();
        $this->paramsPost    = array();
        $this->files         = array();
        $this->raw_post_data = null;

        // Clear outdated headers
        if (isset($this->headers[strtolower(self::CONTENT_TYPE)]))
            unset($this->headers[strtolower(self::CONTENT_TYPE)]);
        if (isset($this->headers[strtolower(self::CONTENT_LENGTH)]))
            unset($this->headers[strtolower(self::CONTENT_LENGTH)]);

        return $this;
    }

    public function getLastRequest()
    {
        return $this->last_request;
    }

    public function getLastResponse()
    {
        return $this->last_response;
    }

    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            try {
                Zend_Loader::loadClass($adapter);
            } catch (Zend_Exception $e) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Unable to load adapter '$adapter': {$e->getMessage()}");
            }

            $adapter = new $adapter;
        }

        if (! $adapter instanceof Zend_Http_Client_Adapter_Interface) {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('Passed adapter is not a HTTP connection adapter');
        }

        $this->adapter = $adapter;
        $config = $this->config;
        unset($config['adapter']);
        $this->adapter->setConfig($config);
    }

    public function request($method = null)
    {
        if (! $this->uri instanceof Zend_Uri_Http) {

            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('No valid URI has been passed to the client');
        }

        if ($method) $this->setMethod($method);
        $this->redirectCounter = 0;
        $response = null;

        // Make sure the adapter is loaded
        if ($this->adapter == null) $this->setAdapter($this->config['adapter']);

        // Send the first request. If redirected, continue.
        do {
            // Clone the URI and add the additional GET parameters to it
            $uri = clone $this->uri;
            if (! empty($this->paramsGet)) {
                $query = $uri->getQuery();
                   if (! empty($query)) $query .= '&';
                $query .= http_build_query($this->paramsGet, null, '&');

                $uri->setQuery($query);
            }

            $body = $this->_prepareBody();
            $headers = $this->_prepareHeaders();

            // Open the connection, send the request and read the response
            $this->adapter->connect($uri->getHost(), $uri->getPort(),
                ($uri->getScheme() == 'https' ? true : false));

            $this->last_request = $this->adapter->write($this->method,
                $uri, $this->config['httpversion'], $headers, $body);

            $response = $this->adapter->read();
            if (! $response) {

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Unable to read response, or response is empty');
            }

            $response = Zend_Http_Response::fromString($response);
            if ($this->config['storeresponse']) $this->last_response = $response;

            // Load cookies into cookie jar
            if (isset($this->cookiejar)) $this->cookiejar->addCookiesFromResponse($response, $uri);

            // If we got redirected, look for the Location header
            if ($response->isRedirect() && ($location = $response->getHeader('location'))) {

                // Check whether we send the exact same request again, or drop the parameters
                // and send a GET request
                if ($response->getStatus() == 303 ||
                   ((! $this->config['strictredirects']) && ($response->getStatus() == 302 ||
                       $response->getStatus() == 301))) {

                    $this->resetParameters();
                    $this->setMethod(self::GET);
                }

                // If we got a well formed absolute URI
                if (Zend_Uri_Http::check($location)) {
                    $this->setHeaders('host', null);
                    $this->setUri($location);

                } else {

                    // Split into path and query and set the query
                    if (strpos($location, '?') !== false) {
                        list($location, $query) = explode('?', $location, 2);
                    } else {
                        $query = '';
                    }
                    $this->uri->setQuery($query);

                    // Else, if we got just an absolute path, set it
                    if(strpos($location, '/') === 0) {
                        $this->uri->setPath($location);

                        // Else, assume we have a relative path
                    } else {
                        // Get the current path directory, removing any trailing slashes
                        $path = $this->uri->getPath();
                        $path = rtrim(substr($path, 0, strrpos($path, '/')), "/");
                        $this->uri->setPath($path . '/' . $location);
                    }
                }
                ++$this->redirectCounter;

            } else {
                // If we didn't get any location, stop redirecting
                break;
            }

        } while ($this->redirectCounter < $this->config['maxredirects']);

        return $response;
    }

    protected function _prepareHeaders()
    {
        $headers = array();

        // Set the host header
        if (! isset($this->headers['host'])) {
            $host = $this->uri->getHost();

            // If the port is not default, add it
            if (! (($this->uri->getScheme() == 'http' && $this->uri->getPort() == 80) ||
                  ($this->uri->getScheme() == 'https' && $this->uri->getPort() == 443))) {
                $host .= ':' . $this->uri->getPort();
            }

            $headers[] = "Host: {$host}";
        }

        // Set the connection header
        if (! isset($this->headers['connection'])) {
            if (! $this->config['keepalive']) $headers[] = "Connection: close";
        }

        // Set the Accept-encoding header if not set - depending on whether
        // zlib is available or not.
        if (! isset($this->headers['accept-encoding'])) {
            if (function_exists('gzinflate')) {
                $headers[] = 'Accept-encoding: gzip, deflate';
            } else {
                $headers[] = 'Accept-encoding: identity';
            }
        }
        
        // Set the Content-Type header
        if ($this->method == self::POST &&
           (! isset($this->headers[strtolower(self::CONTENT_TYPE)]) && isset($this->enctype))) {

            $headers[] = self::CONTENT_TYPE . ': ' . $this->enctype;
        }
        
        // Set the user agent header
        if (! isset($this->headers['user-agent']) && isset($this->config['useragent'])) {
            $headers[] = "User-Agent: {$this->config['useragent']}";
        }

        // Set HTTP authentication if needed
        if (is_array($this->auth)) {
            $auth = self::encodeAuthHeader($this->auth['user'], $this->auth['password'], $this->auth['type']);
            $headers[] = "Authorization: {$auth}";
        }

        // Load cookies from cookie jar
        if (isset($this->cookiejar)) {
            $cookstr = $this->cookiejar->getMatchingCookies($this->uri,
                true, Zend_Http_CookieJar::COOKIE_STRING_CONCAT);

            if ($cookstr) $headers[] = "Cookie: {$cookstr}";
        }

        // Add all other user defined headers
        foreach ($this->headers as $header) {
            list($name, $value) = $header;
            if (is_array($value))
                $value = implode(', ', $value);

            $headers[] = "$name: $value";
        }

        return $headers;
    }

    protected function _prepareBody()
    {
        // According to RFC2616, a TRACE request should not have a body.
        if ($this->method == self::TRACE) {
            return '';
        }

        // If we have raw_post_data set, just use it as the body.
        if (isset($this->raw_post_data)) {
            $this->setHeaders(self::CONTENT_LENGTH, strlen($this->raw_post_data));
            return $this->raw_post_data;
        }

        $body = '';

        // If we have files to upload, force enctype to multipart/form-data
        if (count ($this->files) > 0) $this->setEncType(self::ENC_FORMDATA);

        // If we have POST parameters or files, encode and add them to the body
        if (count($this->paramsPost) > 0 || count($this->files) > 0) {
            switch($this->enctype) {
                case self::ENC_FORMDATA:
                    // Encode body as multipart/form-data
                    $boundary = '---ZENDHTTPCLIENT-' . md5(microtime());
                    $this->setHeaders(self::CONTENT_TYPE, self::ENC_FORMDATA . "; boundary={$boundary}");

                    // Get POST parameters and encode them
                    $params = $this->_getParametersRecursive($this->paramsPost);
                    foreach ($params as $pp) {
                        $body .= self::encodeFormData($boundary, $pp[0], $pp[1]);
                    }

                    // Encode files
                    foreach ($this->files as $name => $file) {
                        $fhead = array(self::CONTENT_TYPE => $file[1]);
                        $body .= self::encodeFormData($boundary, $name, $file[2], $file[0], $fhead);
                    }

                    $body .= "--{$boundary}--\r\n";
                    break;

                case self::ENC_URLENCODED:
                    // Encode body as application/x-www-form-urlencoded
                    $this->setHeaders(self::CONTENT_TYPE, self::ENC_URLENCODED);
                    $body = http_build_query($this->paramsPost, '', '&');
                    break;

                default:

                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("Cannot handle content type '{$this->enctype}' automatically." .
                        " Please use Zend_Http_Client::setRawData to send this kind of content.");
                    break;
            }
        }
        
        // Set the Content-Length if we have a body or if request is POST/PUT
        if ($body || $this->method == self::POST || $this->method == self::PUT) {
            $this->setHeaders(self::CONTENT_LENGTH, strlen($body));
        }

        return $body;
    }

    protected function _getParametersRecursive($parray, $urlencode = false)
    {
        if (! is_array($parray)) return $parray;
        $parameters = array();

        foreach ($parray as $name => $value) {
            if ($urlencode) $name = urlencode($name);

            // If $value is an array, iterate over it
            if (is_array($value)) {
                $name .= ($urlencode ? '%5B%5D' : '[]');
                foreach ($value as $subval) {
                    if ($urlencode) $subval = urlencode($subval);
                    $parameters[] = array($name, $subval);
                }
            } else {
                if ($urlencode) $value = urlencode($value);
                $parameters[] = array($name, $value);
            }
        }

        return $parameters;
    }

    protected function _detectFileMimeType($file)
    {
        $type = null;
        
        // First try with fileinfo functions
        if (function_exists('finfo_open')) {
            if (self::$_fileInfoDb === null) {
                self::$_fileInfoDb = @finfo_open(FILEINFO_MIME);
            }
            
            if (self::$_fileInfoDb) { 
                $type = finfo_file(self::$_fileInfoDb, $file);
            }
            
        } elseif (function_exists('mime_content_type')) {
            $type = mime_content_type($file);
        }
        
        // Fallback to the default application/octet-stream
        if (! $type) {
            $type = 'application/octet-stream';
        }
        
        return $type;
    }

    public static function encodeFormData($boundary, $name, $value, $filename = null, $headers = array()) {
        $ret = "--{$boundary}\r\n" .
            'Content-Disposition: form-data; name="' . $name .'"';

        if ($filename) $ret .= '; filename="' . $filename . '"';
        $ret .= "\r\n";

        foreach ($headers as $hname => $hvalue) {
            $ret .= "{$hname}: {$hvalue}\r\n";
        }
        $ret .= "\r\n";

        $ret .= "{$value}\r\n";

        return $ret;
    }

    public static function encodeAuthHeader($user, $password, $type = self::AUTH_BASIC)
    {
        $authHeader = null;

        switch ($type) {
            case self::AUTH_BASIC:
                // In basic authentication, the user name cannot contain ":"
                if (strpos($user, ':') !== false) {

                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("The user name cannot contain ':' in 'Basic' HTTP authentication");
                }

                $authHeader = 'Basic ' . base64_encode($user . ':' . $password);
                break;

            //case self::AUTH_DIGEST:

            //    break;

            default:

                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception("Not a supported HTTP authentication type: '$type'");
        }

        return $authHeader;
    }
}
