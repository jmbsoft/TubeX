<?php


require_once 'Zend/Uri/Http.php';

class Zend_Http_Cookie
{

    protected $name;

    protected $value;

    protected $expires;

    protected $domain;

    protected $path;

    protected $secure;

    public function __construct($name, $value, $domain, $expires = null, $path = null, $secure = false)
    {
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception("Cookie name cannot contain these characters: =,; \\t\\r\\n\\013\\014 ({$name})");
        }

        if (! $this->name = (string) $name) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Cookies must have a name');
        }

        if (! $this->domain = (string) $domain) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Cookies must have a domain');
        }

        $this->value = (string) $value;
        $this->expires = ($expires === null ? null : (int) $expires);
        $this->path = ($path ? $path : '/');
        $this->secure = $secure;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getExpiryTime()
    {
        return $this->expires;
    }

    public function isSecure()
    {
        return $this->secure;
    }

    public function isExpired($now = null)
    {
        if ($now === null) $now = time();
        if (is_int($this->expires) && $this->expires < $now) {
            return true;
        } else {
            return false;
        }
    }

    public function isSessionCookie()
    {
        return ($this->expires === null);
    }

    public function match($uri, $matchSessionCookies = true, $now = null)
    {
        if (is_string ($uri)) {
            $uri = Zend_Uri_Http::factory($uri);
        }

        // Make sure we have a valid Zend_Uri_Http object
        if (! ($uri->valid() && ($uri->getScheme() == 'http' || $uri->getScheme() =='https'))) {
            require_once 'Zend/Http/Exception.php';
            throw new Zend_Http_Exception('Passed URI is not a valid HTTP or HTTPS URI');
        }

        // Check that the cookie is secure (if required) and not expired
        if ($this->secure && $uri->getScheme() != 'https') return false;
        if ($this->isExpired($now)) return false;
        if ($this->isSessionCookie() && ! $matchSessionCookies) return false;

        // Validate domain and path
        // Domain is validated using tail match, while path is validated using head match
        $domain_preg = preg_quote($this->getDomain(), "/");
        if (! preg_match("/{$domain_preg}$/", $uri->getHost())) return false;
        $path_preg = preg_quote($this->getPath(), "/");
        if (! preg_match("/^{$path_preg}/", $uri->getPath())) return false;

        // If we didn't die until now, return true.
        return true;
    }

    public function __toString()
    {
        return $this->name . '=' . urlencode($this->value) . ';';
    }

    public static function fromString($cookieStr, $ref_uri = null)
    {
        // Set default values
        if (is_string($ref_uri)) {
            $ref_uri = Zend_Uri_Http::factory($ref_uri);
        }

        $name    = '';
        $value   = '';
        $domain  = '';
        $path    = '';
        $expires = null;
        $secure  = false;
        $parts   = explode(';', $cookieStr);

        // If first part does not include '=', fail
        if (strpos($parts[0], '=') === false) return false;

        // Get the name and value of the cookie
        list($name, $value) = explode('=', trim(array_shift($parts)), 2);
        $name  = trim($name);
        $value = urldecode(trim($value));

        // Set default domain and path
        if ($ref_uri instanceof Zend_Uri_Http) {
            $domain = $ref_uri->getHost();
            $path = $ref_uri->getPath();
            $path = substr($path, 0, strrpos($path, '/'));
        }

        // Set other cookie parameters
        foreach ($parts as $part) {
            $part = trim($part);
            if (strtolower($part) == 'secure') {
                $secure = true;
                continue;
            }

            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 2) {
                list($k, $v) = $keyValue;
                switch (strtolower($k))    {
                    case 'expires':
                        $expires = strtotime($v);
                        break;
                    case 'path':
                        $path = $v;
                        break;
                    case 'domain':
                        $domain = $v;
                        break;
                    default:
                        break;
                }
            }
        }

        if ($name !== '') {
            return new self($name, $value, $domain, $expires, $path, $secure);
        } else {
            return false;
        }
    }
}
