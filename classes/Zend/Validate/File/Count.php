<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Count extends Zend_Validate_Abstract
{

    const TOO_MUCH = 'fileCountTooMuch';
    const TOO_LESS = 'fileCountTooLess';


    protected $_messageTemplates = array(
        self::TOO_MUCH => "Too much files, maximum '%max%' are allowed but '%count%' are given",
        self::TOO_LESS => "Too less files, minimum '%min%' are expected but '%count%' are given"
    );

    protected $_messageVariables = array(
        'min'   => '_min',
        'max'   => '_max',
        'count' => '_count'
    );

    protected $_min;

    protected $_max;

    protected $_count;

    protected $_files;

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
            trigger_error('Multiple arguments are deprecated in favor of an array of named arguments', E_USER_NOTICE);
            $options['min'] = func_get_arg(0);
            $options['max'] = func_get_arg(1);
        }

        if (isset($options['min'])) {
            $this->setMin($options);
        }

        if (isset($options['max'])) {
            $this->setMax($options);
        }
    }

    public function getMin()
    {
        return $this->_min;
    }

    public function setMin($min)
    {
        if (is_array($min) and isset($min['min'])) {
            $min = $min['min'];
        }

        if (!is_string($min) and !is_numeric($min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $min = (integer) $min;
        if (($this->_max !== null) && ($min > $this->_max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum file count, but $min >"
                                            . " {$this->_max}");
        }

        $this->_min = $min;
        return $this;
    }

    public function getMax()
    {
        return $this->_max;
    }

    public function setMax($max)
    {
        if (is_array($max) and isset($max['max'])) {
            $max = $max['max'];
        }

        if (!is_string($max) and !is_numeric($max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $max = (integer) $max;
        if (($this->_min !== null) && ($max < $this->_min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum file count, but "
                                            . "$max < {$this->_min}");
        }

        $this->_max = $max;
        return $this;
    }

    public function addFile($file)
    {
        if (is_string($file)) {
            $file = array($file);
        }

        if (is_array($file)) {
            foreach ($file as $name) {
                if (!isset($this->_files[$name])) {
                    $this->_files[$name] = $name;
                }
            }
        }

        return $this;
    }

    public function isValid($value, $file = null)
    {
        $this->addFile($value);
        $this->_count = count($this->_files);
        if (($this->_max !== null) && ($this->_count > $this->_max)) {
            return $this->_throw($file, self::TOO_MUCH);
        }

        if (($this->_min !== null) && ($this->_count < $this->_min)) {
            return $this->_throw($file, self::TOO_LESS);
        }

        return true;
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
