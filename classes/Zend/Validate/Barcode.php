<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Barcode extends Zend_Validate_Abstract
{

    protected $_barcodeValidator;

    public function __construct($barcodeType)
    {
        $this->setType($barcodeType);
    }

    public function setType($barcodeType)
    {
        switch (strtolower($barcodeType)) {
            case 'upc':
            case 'upc-a':
                $className = 'UpcA';
                break;
            case 'ean13':
            case 'ean-13':
                $className = 'Ean13';
                break;
            default:
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Barcode type '$barcodeType' is not supported'");
                break;
        }

        require_once 'Zend/Validate/Barcode/' . $className . '.php';

        $class = 'Zend_Validate_Barcode_' . $className;
        $this->_barcodeValidator = new $class;
    }

    public function isValid($value)
    {
        return call_user_func(array($this->_barcodeValidator, 'isValid'), $value);
    }
}
