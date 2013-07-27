<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Int extends Zend_Validate_Abstract
{
    const NOT_INT = 'notInt';

    protected $_messageTemplates = array(
        self::NOT_INT => "'%value%' does not appear to be an integer"
    );

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
        if (is_bool($value)) {
            $this->_error();
            return false;
        }

        $locale        = localeconv();
        $valueFiltered = str_replace($locale['decimal_point'], '.', $valueString);
        $valueFiltered = str_replace($locale['thousands_sep'], '', $valueFiltered);

        if (strval(intval($valueFiltered)) != $valueFiltered) {
            $this->_error();
            return false;
        }

        return true;
    }

}
