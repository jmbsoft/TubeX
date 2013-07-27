<?php


require_once "Zend/Uri.php";
require_once "Zend/Http/Cookie.php";
require_once "Zend/Http/Response.php";

class Zend_Http_CookieJar
{

    const COOKIE_OBJECT = 0;

    const COOKIE_STRING_ARRAY = 1;

    const COOKIE_STRING_CONCAT = 2;

    protected $cookies = array();

    public function __construct()
    { }

    public function addCookie($cookie, $ref_uri = null)
    {
        if (is_string($cookie)) {
            $cookie = Zend_Http_Cookie::fromString($cookie, $ref_uri);
        }

        if ($cookie instanceof Zend_Http_Cookie) {
            $domain = $cookie->getDomain();
            $path = $cookie->getPath();
            if (! isset($this->cookies[$domain])) $this->cookies[$domain] = array();
            if (! isset($this->cookies[$domain][$path])) $this->cookies[$domain][$path] = array();
            $this->cookies[$domain][$path][$cookie->getName()] = $cookie;
        } else {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Supplient argument is not a valid cookie string or object');
        }
    }

    public function addCookiesFromResponse($response, $ref_uri)
    {
        if (! $response instanceof Zend_Http_Response) {
            require_once 'Zend/Http/Exception.php';        
            throw new Zend_Http_Exception('$response is expected to be a Response object, ' .
                gettype($response) . ' was passed');
        }

        $cookie_hdrs = $response->getHeader('Set-Cookie');

        if (is_array($cookie_hdrs)) {
            foreach ($cookie_hdrs as $cookie) {
                $this->addCookie($cookie, $ref_uri);
            }
        } elseif (is_string($cookie_hdrs)) {
            $this->addCookie($cookie_hdrs, $ref_uri);
        }
    }

    public function getAllCookies($ret_as = self::COOKIE_OBJECT)
    {
        $cookies = $this->_flattenCookiesArray($this->cookies, $ret_as);
        return $cookies;
    }

    public function getMatchingCookies($uri, $matchSessionCookies = true,
        $ret_as = self::COOKIE_OBJECT, $now = null)
    {
        if (is_string($uri)) $uri = Zend_Uri::factory($uri);
        if (! $uri instanceof Zend_Uri_Http) {
            require_once 'Zend/Http/Exception.php';    
            throw new Zend_Http_Exception("Invalid URI string or object passed");
        }

        // Set path
        $path = $uri->getPath();
        $path = substr($path, 0, strrpos($path, '/'));
        if (! $path) $path = '/';

        // First, reduce the array of cookies to only those matching domain and path
        $cookies = $this->_matchDomain($uri->getHost());
        $cookies = $this->_matchPath($cookies, $path);
        $cookies = $this->_flattenCookiesArray($cookies, self::COOKIE_OBJECT);

        // Next, run Cookie->match on all cookies to check secure, time and session mathcing
        $ret = array();
        foreach ($cookies as $cookie)
            if ($cookie->match($uri, $matchSessionCookies, $now))
                $ret[] = $cookie;

        // Now, use self::_flattenCookiesArray again - only to convert to the return format ;)
        $ret = $this->_flattenCookiesArray($ret, $ret_as);

        return $ret;
    }

    public function getCookie($uri, $cookie_name, $ret_as = self::COOKIE_OBJECT)
    {
        if (is_string($uri)) {
            $uri = Zend_Uri::factory($uri);
        }

        if (! $uri instanceof Zend_Uri_Http) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Invalid URI specified');
        }

        // Get correct cookie path
        $path = $uri->getPath();
        $path = substr($path, 0, strrpos($path, '/'));
        if (! $path) $path = '/';

        if (isset($this->cookies[$uri->getHost()][$path][$cookie_name])) {
            $cookie = $this->cookies[$uri->getHost()][$path][$cookie_name];

            switch ($ret_as) {
                case self::COOKIE_OBJECT:
                    return $cookie;
                    break;

                case self::COOKIE_STRING_ARRAY:
                case self::COOKIE_STRING_CONCAT:
                    return $cookie->__toString();
                    break;

                default:
                    require_once 'Zend/Http/Exception.php';
                    throw new Zend_Http_Exception("Invalid value passed for \$ret_as: {$ret_as}");
                    break;
            }
        } else {
            return false;
        }
    }

    protected function _flattenCookiesArray($ptr, $ret_as = self::COOKIE_OBJECT) {
        if (is_array($ptr)) {
            $ret = ($ret_as == self::COOKIE_STRING_CONCAT ? '' : array());
            foreach ($ptr as $item) {
                if ($ret_as == self::COOKIE_STRING_CONCAT) {
                    $ret .= $this->_flattenCookiesArray($item, $ret_as);
                } else {
                    $ret = array_merge($ret, $this->_flattenCookiesArray($item, $ret_as));
                }
            }
            return $ret;
        } elseif ($ptr instanceof Zend_Http_Cookie) {
            switch ($ret_as) {
                case self::COOKIE_STRING_ARRAY:
                    return array($ptr->__toString());
                    break;

                case self::COOKIE_STRING_CONCAT:
                    return $ptr->__toString();
                    break;

                case self::COOKIE_OBJECT:
                default:
                    return array($ptr);
                    break;
            }
        }

        return null;
    }

    protected function _matchDomain($domain) {
        $ret = array();

        foreach (array_keys($this->cookies) as $cdom) {
            $regex = "/" . preg_quote($cdom, "/") . "$/i";
            if (preg_match($regex, $domain)) $ret[$cdom] = &$this->cookies[$cdom];
        }

        return $ret;
    }

    protected function _matchPath($domains, $path) {
        $ret = array();
        if (substr($path, -1) != '/') $path .= '/';

        foreach ($domains as $dom => $paths_array) {
            foreach (array_keys($paths_array) as $cpath) {
                $regex = "|^" . preg_quote($cpath, "|") . "|i";
                if (preg_match($regex, $path)) {
                    if (! isset($ret[$dom])) $ret[$dom] = array();
                    $ret[$dom][$cpath] = &$paths_array[$cpath];
                }
            }
        }

        return $ret;
    }

    public static function fromResponse(Zend_Http_Response $response, $ref_uri)
    {
        $jar = new self();
        $jar->addCookiesFromResponse($response, $ref_uri);
        return $jar;
    }
}
