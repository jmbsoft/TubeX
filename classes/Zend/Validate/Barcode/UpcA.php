<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Barcode_UpcA extends Zend_Validate_Abstract
{

    const INVALID = 'invalid';

    const INVALID_LENGTH = 'invalidLength';

    protected $_messageTemplates = array(
        self::INVALID        => "'%value%' is an invalid UPC-A barcode",
        self::INVALID_LENGTH => "'%value%' should be 12 characters",
    );

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (strlen($valueString) !== 12) {
            $this->_error(self::INVALID_LENGTH);
            return false;
        }

        $barcode = substr($valueString, 0, -1);
        $oddSum  = 0;
        $evenSum = 0;

        for ($i = 0; $i < 11; $i++) {
            if ($i % 2 === 0) {
                $oddSum += $barcode[$i] * 3;
            } elseif ($i % 2 === 1) {
                $evenSum += $barcode[$i];
            }
        }

        $calculation = ($oddSum + $evenSum) % 10;
        $checksum    = ($calculation === 0) ? 0 : 10 - $calculation;

        if ($valueString[11] != $checksum) {
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
