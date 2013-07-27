<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Float extends Zend_Validate_Abstract
{

    const NOT_FLOAT = 'notFloat';

    protected $_messageTemplates = array(
        self::NOT_FLOAT => "'%value%' does not appear to be a float"
    );

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        $locale = localeconv();

        $valueFiltered = str_replace($locale['thousands_sep'], '', $valueString);
        $valueFiltered = str_replace($locale['decimal_point'], '.', $valueFiltered);

        if (strval(floatval($valueFiltered)) != $valueFiltered) {
            $this->_error();
            return false;
        }

        return true;
    }

}
