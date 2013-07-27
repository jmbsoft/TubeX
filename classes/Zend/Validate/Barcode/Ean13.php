<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Barcode_Ean13 extends Zend_Validate_Abstract
{

    const INVALID = 'invalid';

    const INVALID_LENGTH = 'invalidLength';

    const NOT_NUMERIC = 'ean13NotNumeric';

    protected $_messageTemplates = array(
        self::INVALID        => "'%value%' is an invalid EAN-13 barcode",
        self::INVALID_LENGTH => "'%value%' should be 13 characters",
        self::NOT_NUMERIC    => "'%value%' should contain only numeric characters",
    );

    public function isValid($value)
    {
        if (false === ctype_digit($value)) {
            $this->_error(self::NOT_NUMERIC);
            return false;
        }

        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (strlen($valueString) !== 13) {
            $this->_error(self::INVALID_LENGTH);
            return false;
        }

        $barcode = strrev(substr($valueString, 0, -1));
        $oddSum  = 0;
        $evenSum = 0;

        for ($i = 0; $i < 12; $i++) {
            if ($i % 2 === 0) {
                $oddSum += $barcode[$i] * 3;
            } elseif ($i % 2 === 1) {
                $evenSum += $barcode[$i];
            }
        }

        $calculation = ($oddSum + $evenSum) % 10;
        $checksum    = ($calculation === 0) ? 0 : 10 - $calculation;

        if ($valueString[12] != $checksum) {
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
