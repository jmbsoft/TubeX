<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Between extends Zend_Validate_Abstract
{

    const NOT_BETWEEN        = 'notBetween';

    const NOT_BETWEEN_STRICT = 'notBetweenStrict';

    protected $_messageTemplates = array(
        self::NOT_BETWEEN        => "'%value%' is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "'%value%' is not strictly between '%min%' and '%max%'"
    );

    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    protected $_min;

    protected $_max;

    protected $_inclusive;

    public function __construct($min, $max, $inclusive = true)
    {
        $this->setMin($min)
             ->setMax($max)
             ->setInclusive($inclusive);
    }

    public function getMin()
    {
        return $this->_min;
    }

    public function setMin($min)
    {
        $this->_min = $min;
        return $this;
    }

    public function getMax()
    {
        return $this->_max;
    }

    public function setMax($max)
    {
        $this->_max = $max;
        return $this;
    }

    public function getInclusive()
    {
        return $this->_inclusive;
    }

    public function setInclusive($inclusive)
    {
        $this->_inclusive = $inclusive;
        return $this;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_inclusive) {
            if ($this->_min > $value || $value > $this->_max) {
                $this->_error(self::NOT_BETWEEN);
                return false;
            }
        } else {
            if ($this->_min >= $value || $value >= $this->_max) {
                $this->_error(self::NOT_BETWEEN_STRICT);
                return false;
            }
        }
        return true;
    }

}
