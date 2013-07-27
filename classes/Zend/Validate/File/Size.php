<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Size extends Zend_Validate_Abstract
{

    const TOO_BIG   = 'fileSizeTooBig';
    const TOO_SMALL = 'fileSizeTooSmall';
    const NOT_FOUND = 'fileSizeNotFound';


    protected $_messageTemplates = array(
        self::TOO_BIG   => "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected",
        self::TOO_SMALL => "Minimum expected size for file '%value%' is '%min%' but '%size%' detected",
        self::NOT_FOUND => "The file '%value%' could not be found"
    );

    protected $_messageVariables = array(
        'min'  => '_min',
        'max'  => '_max',
        'size' => '_size',
    );

    protected $_min;

    protected $_max;

    protected $_size;

    protected $_useByteString = true;

    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_string($options) || is_numeric($options)) {
            $options = array('max' => $options);
        } elseif (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
            trigger_error('Multiple constructor options are deprecated in favor of a single options array', E_USER_NOTICE);
            $argv = func_get_args();
            array_shift($argv);
            $options['max'] = array_shift($argv);
            if (!empty($argv)) {
                $options['bytestring'] = array_shift($argv);
            }
        }

        if (isset($options['bytestring'])) {
            $this->setUseByteString($options['bytestring']);
        }

        if (isset($options['min'])) {
            $this->setMin($options['min']);
        }

        if (isset($options['max'])) {
            $this->setMax($options['max']);
        }
    }

    public function setUseByteString($byteString = true)
    {
        $this->_useByteString = (bool) $byteString;
        return $this;
    }

    public function useByteString()
    {
        return $this->_useByteString;
    }

    public function getMin($raw = false)
    {
        $min = $this->_min;
        if (!$raw && $this->useByteString()) {
            $min = $this->_toByteString($min);
        }

        return $min;
    }

    public function setMin($min)
    {
        if (!is_string($min) and !is_numeric($min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $min = (integer) $this->_fromByteString($min);
        $max = $this->getMax(true);
        if (($max !== null) && ($min > $max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum filesize, but $min >"
                                            . " $max");
        }

        $this->_min = $min;
        return $this;
    }

    public function getMax($raw = false)
    {
        $max = $this->_max;
        if (!$raw && $this->useByteString()) {
            $max = $this->_toByteString($max);
        }

        return $max;
    }

    public function setMax($max)
    {
        if (!is_string($max) && !is_numeric($max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $max = (integer) $this->_fromByteString($max);
        $min = $this->getMin(true);
        if (($min !== null) && ($max < $min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum filesize, but "
                                            . "$max < $min");
        }

        $this->_max = $max;
        return $this;
    }

    protected function _getSize()
    {
        return $this->_size;
    }

    protected function _setSize($size)
    {
        $this->_size = $size;
        return $this;
    }

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        // limited to 4GB files
        $size = sprintf("%u", @filesize($value));

        // Check to see if it's smaller than min size
        $min = $this->getMin(true);
        $max = $this->getMax(true);
        if (($min !== null) && ($size < $min)) {
            if ($this->useByteString()) {
                $this->_min  = $this->_toByteString($min);
                $this->_size = $this->_toByteString($size);
                $this->_throw($file, self::TOO_SMALL);
                $this->_min  = $min;
                $this->_size = $size;
            } else {
                $this->_throw($file, self::TOO_SMALL);
            }
        }

        // Check to see if it's larger than max size
        if (($max !== null) && ($max < $size)) {
            if ($this->useByteString()) {
                $this->_max  = $this->_toByteString($max);
                $this->_size = $this->_toByteString($size);
                $this->_throw($file, self::TOO_BIG);
                $this->_max  = $max;
                $this->_size = $size;
            } else {
                $this->_throw($file, self::TOO_BIG);
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
    }

    protected function _toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    protected function _fromByteString($size)
    {
        if (is_numeric($size)) {
            return (integer) $size;
        }

        $type  = trim(substr($size, -2, 1));

        $value = substr($size, 0, -1);
        if (!is_numeric($value)) {
            $value = substr($value, 0, -1);
        }

        switch (strtoupper($type)) {
            case 'Y':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'Z':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'E':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'P':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'T':
                $value *= (1024 * 1024 * 1024 * 1024);
                break;
            case 'G':
                $value *= (1024 * 1024 * 1024);
                break;
            case 'M':
                $value *= (1024 * 1024);
                break;
            case 'K':
                $value *= 1024;
                break;
            default:
                break;
        }

        return $value;
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
