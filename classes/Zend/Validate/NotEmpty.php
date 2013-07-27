<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_NotEmpty extends Zend_Validate_Abstract
{
    const IS_EMPTY = 'isEmpty';

    protected $_messageTemplates = array(
        self::IS_EMPTY => "Value is required and can't be empty"
    );

    public function isValid($value)
    {
        $this->_setValue((string) $value);

        if (is_string($value)
            && (('' === $value)
                || preg_match('/^\s+$/s', $value))
        ) {
            $this->_error();
            return false;
        } elseif (!is_string($value) && empty($value)) {
            $this->_error();
            return false;
        }

        return true;
    }

}
