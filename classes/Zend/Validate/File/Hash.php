<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Hash extends Zend_Validate_Abstract
{

    const DOES_NOT_MATCH = 'fileHashDoesNotMatch';
    const NOT_DETECTED   = 'fileHashHashNotDetected';
    const NOT_FOUND      = 'fileHashNotFound';

    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "The file '%value%' does not match the given hashes",
        self::NOT_DETECTED   => "There was no hash detected for the given file",
        self::NOT_FOUND      => "The file '%value%' could not be found"
    );

    protected $_hash;

    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_scalar($options)) {
            $options = array('hash1' => $options);
        } elseif (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
            trigger_error('Multiple constructor options are deprecated in favor of a single options array', E_USER_NOTICE);
            $options['algorithm'] = func_get_arg(1);
        }

        $this->setHash($options);
    }

    public function getHash()
    {
        return $this->_hash;
    }

    public function setHash($options)
    {
        $this->_hash  = null;
        $this->addHash($options);

        return $this;
    }

    public function addHash($options)
    {
        if (is_string($options)) {
            $options = array($options);
        } else if (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("False parameter given");
        }

        $known = hash_algos();
        if (!isset($options['algorithm'])) {
            $algorithm = 'crc32';
        } else {
            $algorithm = $options['algorithm'];
            unset($options['algorithm']);
        }

        if (!in_array($algorithm, $known)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Unknown algorithm '{$algorithm}'");
        }

        foreach ($options as $value) {
            $this->_hash[$value] = $algorithm;
        }

        return $this;
    }

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        $algos  = array_unique(array_values($this->_hash));
        $hashes = array_unique(array_keys($this->_hash));
        foreach ($algos as $algorithm) {
            $filehash = hash_file($algorithm, $value);
            if ($filehash === false) {
                return $this->_throw($file, self::NOT_DETECTED);
            }

            foreach($hashes as $hash) {
                if ($filehash === $hash) {
                    return true;
                }
            }
        }

        return $this->_throw($file, self::DOES_NOT_MATCH);
    }

    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}
