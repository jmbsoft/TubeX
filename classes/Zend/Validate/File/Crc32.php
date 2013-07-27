<?php


require_once 'Zend/Validate/File/Hash.php';

class Zend_Validate_File_Crc32 extends Zend_Validate_File_Hash
{

    const DOES_NOT_MATCH = 'fileCrc32DoesNotMatch';
    const NOT_DETECTED   = 'fileCrc32NotDetected';
    const NOT_FOUND      = 'fileCrc32NotFound';

    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "The file '%value%' does not match the given crc32 hashes",
        self::NOT_DETECTED   => "There was no crc32 hash detected for the given file",
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

        $this->setCrc32($options);
    }

    public function getCrc32()
    {
        return $this->getHash();
    }

    public function setHash($options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }

        $options['algorithm'] = 'crc32';
        parent::setHash($options);
        return $this;
    }

    public function setCrc32($options)
    {
        $this->setHash($options);
        return $this;
    }

    public function addHash($options)
    {
        if (!is_array($options)) {
            $options = array($options);
        }

        $options['algorithm'] = 'crc32';
        parent::addHash($options);
        return $this;
    }

    public function addCrc32($options)
    {
        $this->addHash($options);
        return $this;
    }

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        $hashes = array_unique(array_keys($this->_hash));
        $filehash = hash_file('crc32', $value);
        if ($filehash === false) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        foreach($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        return $this->_throw($file, self::DOES_NOT_MATCH);
    }
}