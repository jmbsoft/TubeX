<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_GreaterThan extends Zend_Validate_Abstract
{

    const NOT_GREATER = 'notGreaterThan';

    protected $_messageTemplates = array(
        self::NOT_GREATER => "'%value%' is not greater than '%min%'"
    );

    protected $_messageVariables = array(
        'min' => '_min'
    );

    protected $_min;

    public function __construct($min)
    {
        $this->setMin($min);
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

    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->_min >= $value) {
            $this->_error();
            return false;
        }
        return true;
    }

}
