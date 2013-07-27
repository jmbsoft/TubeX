<?php


class Zend_Registry extends ArrayObject
{

    private static $_registryClassName = 'Zend_Registry';

    private static $_registry = null;

    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    public static function setInstance(Zend_Registry $registry)
    {
        if (self::$_registry !== null) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Registry is already initialized');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

    public static function setClassName($registryClassName = 'Zend_Registry')
    {
        if (self::$_registry !== null) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception('Registry is already initialized');
        }

        if (!is_string($registryClassName)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("Argument is not a class name");
        }

        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($registryClassName);

        self::$_registryClassName = $registryClassName;
    }

    public static function _unsetInstance()
    {
        self::$_registry = null;
    }

    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            require_once 'Zend/Exception.php';
            throw new Zend_Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
    {
        parent::__construct($array, $flags);
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }

}
