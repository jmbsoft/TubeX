<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_LessThan extends Zend_Validate_Abstract
{

    const NOT_LESS = 'notLessThan';

    protected $_messageTemplates = array(
        self::NOT_LESS => "'%value%' is not less than '%max%'"
    );

    protected $_messageVariables = array(
        'max' => '_max'
    );

    protected $_max;

    public function __construct($max)
    {
        $this->setMax($max);
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

    public function isValid($value)
    {
        $this->_setValue($value);
        if ($this->_max <= $value) {
            $this->_error();
            return false;
        }
        return true;
    }

}
