<?php


require_once 'Zend/Validate/File/Hash.php';

class Zend_Validate_File_Sha1 extends Zend_Validate_File_Hash
{

    const DOES_NOT_MATCH = 'fileSha1DoesNotMatch';
    const NOT_DETECTED   = 'fileSha1NotDetected';
    const NOT_FOUND      = 'fileSha1NotFound';

    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "The file '%value%' does not match the given sha1 hashes",
        self::NOT_DETECTED   => "There was no sha1 hash detected for the given file",
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

        $this->setHash($options);
    }

    public function getSha1()
    {
        return $this->getHash();
    }

    public function setHash($options)
    {
        if (!is_array($options)) {
            $options = (array) $options;
        }

        $options['algorithm'] = 'sha1';
        parent::setHash($options);
        return $this;
    }

    public function setSha1($options)
    {
        $this->setHash($options);
        return $this;
    }

    public function addHash($options)
    {
        if (!is_array($options)) {
            $options = (array) $options;
        }

        $options['algorithm'] = 'sha1';
        parent::addHash($options);
        return $this;
    }

    public function addSha1($options)
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
        $filehash = hash_file('sha1', $value);
        if ($filehash === false) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        foreach ($hashes as $hash) {
            if ($filehash === $hash) {
                return true;
            }
        }

        return $this->_throw($file, self::DOES_NOT_MATCH);
    }
}
