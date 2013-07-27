<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_StringLength extends Zend_Validate_Abstract
{
    const TOO_SHORT = 'stringLengthTooShort';
    const TOO_LONG  = 'stringLengthTooLong';

    protected $_messageTemplates = array(
        self::TOO_SHORT => "'%value%' is less than %min% characters long",
        self::TOO_LONG  => "'%value%' is greater than %max% characters long"
    );

    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    protected $_min;

    protected $_max;

    protected $_encoding;

    public function __construct($min = 0, $max = null, $encoding = null)
    {
        $this->setMin($min);
        $this->setMax($max);
        $this->setEncoding($encoding);
    }

    public function getMin()
    {
        return $this->_min;
    }

    public function setMin($min)
    {
        if (null !== $this->_max && $min > $this->_max) {

            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum length, but $min >"
                                            . " $this->_max");
        }
        $this->_min = max(0, (integer) $min);
        return $this;
    }

    public function getMax()
    {
        return $this->_max;
    }

    public function setMax($max)
    {
        if (null === $max) {
            $this->_max = null;
        } else if ($max < $this->_min) {

            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum length, but "
                                            . "$max < $this->_min");
        } else {
            $this->_max = (integer) $max;
        }

        return $this;
    }

    public function getEncoding()
    {
        return $this->_encoding;
    }

    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            $orig   = iconv_get_encoding('internal_encoding');
            $result = iconv_set_encoding('internal_encoding', $encoding);
            if (!$result) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('Given encoding not supported on this OS!');
            }

            iconv_set_encoding('internal_encoding', $orig);
        }

        $this->_encoding = $encoding;
        return $this;
    }

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
        if ($this->_encoding !== null) {
            $length = iconv_strlen($valueString, $this->_encoding);
        } else {
            $length = iconv_strlen($valueString);
        }

        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }

        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }

        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}
