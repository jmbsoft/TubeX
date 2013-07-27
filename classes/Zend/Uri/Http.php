<?php


require_once 'Zend/Uri.php';

require_once 'Zend/Validate/Hostname.php';

class Zend_Uri_Http extends Zend_Uri
{

    const CHAR_ALNUM    = 'A-Za-z0-9';
    const CHAR_MARK     = '-_.!~*\'()\[\]';
    const CHAR_RESERVED = ';\/?:@&=+$,';
    const CHAR_SEGMENT  = ':@&=+$,;';
    const CHAR_UNWISE   = '{}|\\\\^`';

    protected $_username = '';

    protected $_password = '';

    protected $_host = '';

    protected $_port = '';

    protected $_path = '';

    protected $_query = '';

    protected $_fragment = '';

    protected $_regex = array();

    protected function __construct($scheme, $schemeSpecific = '')
    {
        // Set the scheme
        $this->_scheme = $scheme;

        // Set up grammar rules for validation via regular expressions. These
        // are to be used with slash-delimited regular expression strings.
        
        // Escaped special characters (eg. '%25' for '%') 
        $this->_regex['escaped']    = '%[[:xdigit:]]{2}';
        
        // Unreserved characters
        $this->_regex['unreserved'] = '[' . self::CHAR_ALNUM . self::CHAR_MARK . ']';
        
        // Segment can use escaped, unreserved or a set of additional chars
        $this->_regex['segment']    = '(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . self::CHAR_SEGMENT . '])*';
        
        // Path can be a series of segmets char strings seperated by '/'
        $this->_regex['path']       = '(?:\/(?:' . $this->_regex['segment'] . ')?)+';
        
        // URI characters can be escaped, alphanumeric, mark or reserved chars
        $this->_regex['uric']       = '(?:' . $this->_regex['escaped'] . '|[' .  
            self::CHAR_ALNUM . self::CHAR_MARK . self::CHAR_RESERVED . 
            
        // If unwise chars are allowed, add them to the URI chars class
            (self::$_config['allow_unwise'] ? self::CHAR_UNWISE : '') . '])';
                                    
        // If no scheme-specific part was supplied, the user intends to create
        // a new URI with this object.  No further parsing is required.
        if (strlen($schemeSpecific) === 0) {
            return;
        }

        // Parse the scheme-specific URI parts into the instance variables.
        $this->_parseUri($schemeSpecific);

        // Validate the URI
        if ($this->valid() === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Invalid URI supplied');
        }
    }

    public static function fromString($uri)
    {
        if (is_string($uri) === false) {
            throw new InvalidArgumentException('$uri is not a string');
        }

        $uri            = explode(':', $uri, 2);
        $scheme         = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) === true ? $uri[1] : '';

        if (in_array($scheme, array('http', 'https')) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Invalid scheme: '$scheme'");
        }

        $schemeHandler = new Zend_Uri_Http($scheme, $schemeSpecific);
        return $schemeHandler;
    }

    protected function _parseUri($schemeSpecific)
    {
        // High-level decomposition parser
        $pattern = '~^((//)([^/?#]*))([^?#]*)(\?([^#]*))?(#(.*))?$~';
        $status  = @preg_match($pattern, $schemeSpecific, $matches);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: scheme-specific decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if ($status === false) {
            return;
        }

        // Save URI components that need no further decomposition
        $this->_path     = isset($matches[4]) === true ? $matches[4] : '';
        $this->_query    = isset($matches[6]) === true ? $matches[6] : '';
        $this->_fragment = isset($matches[8]) === true ? $matches[8] : '';

        // Additional decomposition to get username, password, host, and port
        $combo   = isset($matches[3]) === true ? $matches[3] : '';
        $pattern = '~^(([^:@]*)(:([^@]*))?@)?([^:]+)(:(.*))?$~';
        $status  = @preg_match($pattern, $combo, $matches);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: authority decomposition failed');
        }

        // Failed decomposition; no further processing needed
        if ($status === false) {
            return;
        }

        // Save remaining URI components
        $this->_username = isset($matches[2]) === true ? $matches[2] : '';
        $this->_password = isset($matches[4]) === true ? $matches[4] : '';
        $this->_host     = isset($matches[5]) === true ? $matches[5] : '';
        $this->_port     = isset($matches[7]) === true ? $matches[7] : '';

    }

    public function getUri()
    {
        if ($this->valid() === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('One or more parts of the URI are invalid');
        }

        $password = strlen($this->_password) > 0 ? ":$this->_password" : '';
        $auth     = strlen($this->_username) > 0 ? "$this->_username$password@" : '';
        $port     = strlen($this->_port) > 0 ? ":$this->_port" : '';
        $query    = strlen($this->_query) > 0 ? "?$this->_query" : '';
        $fragment = strlen($this->_fragment) > 0 ? "#$this->_fragment" : '';

        return $this->_scheme
             . '://'
             . $auth
             . $this->_host
             . $port
             . $this->_path
             . $query
             . $fragment;
    }

    public function valid()
    {
        // Return true if and only if all parts of the URI have passed validation
        return $this->validateUsername()
           and $this->validatePassword()
           and $this->validateHost()
           and $this->validatePort()
           and $this->validatePath()
           and $this->validateQuery()
           and $this->validateFragment();
    }

    public function getUsername()
    {
        return strlen($this->_username) > 0 ? $this->_username : false;
    }

    public function validateUsername($username = null)
    {
        if ($username === null) {
            $username = $this->_username;
        }

        // If the username is empty, then it is considered valid
        if (strlen($username) === 0) {
            return true;
        }

        // Check the username against the allowed values
        $status = @preg_match('/^(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . ';:&=+$,' . '])+$/', $username);
                            
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: username validation failed');
        }

        return $status === 1;
    }

    public function setUsername($username)
    {
        if ($this->validateUsername($username) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Username \"$username\" is not a valid HTTP username");
        }

        $oldUsername     = $this->_username;
        $this->_username = $username;

        return $oldUsername;
    }

    public function getPassword()
    {
        return strlen($this->_password) > 0 ? $this->_password : false;
    }

    public function validatePassword($password = null)
    {
        if ($password === null) {
            $password = $this->_password;
        }

        // If the password is empty, then it is considered valid
        if (strlen($password) === 0) {
            return true;
        }

        // If the password is nonempty, but there is no username, then it is considered invalid
        if (strlen($password) > 0 and strlen($this->_username) === 0) {
            return false;
        }

        // Check the password against the allowed values
        $status = @preg_match('/^(?:' . $this->_regex['escaped'] . '|[' .
            self::CHAR_ALNUM . self::CHAR_MARK . ';:&=+$,' . '])+$/', $password);
            
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: password validation failed.');
        }

        return $status == 1;
    }

    public function setPassword($password)
    {
        if ($this->validatePassword($password) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Password \"$password\" is not a valid HTTP password.");
        }

        $oldPassword     = $this->_password;
        $this->_password = $password;

        return $oldPassword;
    }

    public function getHost()
    {
        return strlen($this->_host) > 0 ? $this->_host : false;
    }

    public function validateHost($host = null)
    {
        if ($host === null) {
            $host = $this->_host;
        }

        // If the host is empty, then it is considered invalid
        if (strlen($host) === 0) {
            return false;
        }

        // Check the host against the allowed values; delegated to Zend_Filter.
        $validate = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_ALL);

        return $validate->isValid($host);
    }

    public function setHost($host)
    {
        if ($this->validateHost($host) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Host \"$host\" is not a valid HTTP host");
        }

        $oldHost     = $this->_host;
        $this->_host = $host;

        return $oldHost;
    }

    public function getPort()
    {
        return strlen($this->_port) > 0 ? $this->_port : false;
    }

    public function validatePort($port = null)
    {
        if ($port === null) {
            $port = $this->_port;
        }

        // If the port is empty, then it is considered valid
        if (strlen($port) === 0) {
            return true;
        }

        // Check the port against the allowed values
        return ctype_digit((string) $port) and 1 <= $port and $port <= 65535;
    }

    public function setPort($port)
    {
        if ($this->validatePort($port) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Port \"$port\" is not a valid HTTP port.");
        }

        $oldPort     = $this->_port;
        $this->_port = $port;

        return $oldPort;
    }

    public function getPath()
    {
        return strlen($this->_path) > 0 ? $this->_path : '/';
    }

    public function validatePath($path = null)
    {
        if ($path === null) {
            $path = $this->_path;
        }

        // If the path is empty, then it is considered valid
        if (strlen($path) === 0) {
            return true;
        }

        // Determine whether the path is well-formed
        $pattern = '/^' . $this->_regex['path'] . '$/';
        $status  = @preg_match($pattern, $path);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: path validation failed');
        }

        return (boolean) $status;
    }

    public function setPath($path)
    {
        if ($this->validatePath($path) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Path \"$path\" is not a valid HTTP path");
        }

        $oldPath     = $this->_path;
        $this->_path = $path;

        return $oldPath;
    }

    public function getQuery()
    {
        return strlen($this->_query) > 0 ? $this->_query : false;
    }

    public function validateQuery($query = null)
    {
        if ($query === null) {
            $query = $this->_query;
        }

        // If query is empty, it is considered to be valid
        if (strlen($query) === 0) {
            return true;
        }

        // Determine whether the query is well-formed
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status  = @preg_match($pattern, $query);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: query validation failed');
        }

        return $status == 1;
    }

    public function setQuery($query)
    {
        $oldQuery = $this->_query;

        // If query is empty, set an empty string
        if (empty($query) === true) {
            $this->_query = '';
            return $oldQuery;
        }

        // If query is an array, make a string out of it
        if (is_array($query) === true) {
            $query = http_build_query($query, '', '&');
        } else {
            // If it is a string, make sure it is valid. If not parse and encode it
            $query = (string) $query;
            if ($this->validateQuery($query) === false) {
                parse_str($query, $queryArray);
                $query = http_build_query($queryArray, '', '&');
            }
        }

        // Make sure the query is valid, and set it
        if ($this->validateQuery($query) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("'$query' is not a valid query string");
        }

        $this->_query = $query;

        return $oldQuery;
    }

    public function getFragment()
    {
        return strlen($this->_fragment) > 0 ? $this->_fragment : false;
    }

    public function validateFragment($fragment = null)
    {
        if ($fragment === null) {
            $fragment = $this->_fragment;
        }

        // If fragment is empty, it is considered to be valid
        if (strlen($fragment) === 0) {
            return true;
        }

        // Determine whether the fragment is well-formed
        $pattern = '/^' . $this->_regex['uric'] . '*$/';
        $status  = @preg_match($pattern, $fragment);
        if ($status === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Internal error: fragment validation failed');
        }

        return (boolean) $status;
    }

    public function setFragment($fragment)
    {
        if ($this->validateFragment($fragment) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception("Fragment \"$fragment\" is not a valid HTTP fragment");
        }

        $oldFragment     = $this->_fragment;
        $this->_fragment = $fragment;

        return $oldFragment;
    }
}
