<?php


require_once 'Zend/Loader.php';

abstract class Zend_Uri
{

    protected $_scheme = '';

    static protected $_config = array(
        'allow_unwise' => false
    );

    public function __toString()
    {
        return $this->getUri();
    }

    public static function check($uri)
    {
        try {
            $uri = self::factory($uri);
        } catch (Exception $e) {
            return false;
        }

        return $uri->valid();
    }

    public static function factory($uri = 'http')
    {
        // Separate the scheme from the scheme-specific parts
        $uri            = explode(':', $uri, 2);
        $scheme         = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) === true ? $uri[1] : '';

        if (strlen($scheme) === 0) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('An empty string was supplied for the scheme');
        }

        // Security check: $scheme is used to load a class file, so only alphanumerics are allowed.
        if (ctype_alnum($scheme) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Illegal scheme supplied, only alphanumeric characters are permitted');
        }

        switch ($scheme) {
            case 'http':
                // Break intentionally omitted
            case 'https':
                $className = 'Zend_Uri_Http';
                break;

            case 'mailto':
                // TODO
            default:
                require_once 'Zend/Uri/Exception.php';
                throw new Zend_Uri_Exception("Scheme \"$scheme\" is not supported");
                break;
        }

        Zend_Loader::loadClass($className);
        $schemeHandler = new $className($scheme, $schemeSpecific);

        return $schemeHandler;
    }

    public function getScheme()
    {
        if (empty($this->_scheme) === false) {
            return $this->_scheme;
        } else {
            return false;
        }
    }

    static public function setConfig(array $config)
    {
        foreach ($config as $k => $v) {
            self::$_config[$k] = $v;
        }
    }

    abstract protected function __construct($scheme, $schemeSpecific = '');

    abstract public function getUri();

    abstract public function valid();
}
