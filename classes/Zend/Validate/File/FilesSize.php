<?php


require_once 'Zend/Validate/File/Size.php';

class Zend_Validate_File_FilesSize extends Zend_Validate_File_Size
{

    const TOO_BIG      = 'fileFilesSizeTooBig';
    const TOO_SMALL    = 'fileFilesSizeTooSmall';
    const NOT_READABLE = 'fileFilesSizeNotReadable';

    protected $_messageTemplates = array(
        self::TOO_BIG      => "All files in sum should have a maximum size of '%max%' but '%size%' were detected",
        self::TOO_SMALL    => "All files in sum should have a minimum size of '%min%' but '%size%' were detected",
        self::NOT_READABLE => "One or more files can not be read"
    );

    protected $_files;

    public function __construct($options)
    {
        $this->_files = array();
        $this->_setSize(0);

        if (1 < func_num_args()) {
            trigger_error('Multiple constructor options are deprecated in favor of a single options array', E_USER_NOTICE);
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } elseif (is_scalar($options)) {
                $options = array('min' => $options);
            } elseif (!is_array($options)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Invalid options to validator provided');
            }

            $argv = func_get_args();
            array_shift($argv);
            $options['max'] = array_shift($argv);
            if (!empty($argv)) {
                $options['bytestring'] = array_shift($argv);
            }
        }

        parent::__construct($options);
    }

    public function isValid($value, $file = null)
    {
        require_once 'Zend/Loader.php';
        if (is_string($value)) {
            $value = array($value);
        }

        $min  = $this->getMin(true);
        $max  = $this->getMax(true);
        $size = $this->_getSize();
        foreach ($value as $files) {
            // Is file readable ?
            if (!Zend_Loader::isReadable($files)) {
                $this->_throw($file, self::NOT_READABLE);
                continue;
            }

            if (!isset($this->_files[$files])) {
                $this->_files[$files] = $files;
            } else {
                // file already counted... do not count twice
                continue;
            }

            // limited to 2GB files
            $size += @filesize($files);
            $this->_setSize($size);
            if (($max !== null) && ($max < $size)) {
                if ($this->useByteString()) {
                    $this->setMax($this->_toByteString($max));
                    $this->_throw($file, self::TOO_BIG);
                    $this->setMax($max);
                } else {
                    $this->_throw($file, self::TOO_BIG);
                }
            }
        }

        // Check that aggregate files are >= minimum size
        if (($min !== null) && ($size < $min)) {
            if ($this->useByteString()) {
                $this->setMin($this->_toByteString($min));
                $this->_throw($file, self::TOO_SMALL);
                $this->setMin($min);
            } else {
                $this->_throw($file, self::TOO_SMALL);
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
    }
}
