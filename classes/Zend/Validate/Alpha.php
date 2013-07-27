<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Alpha extends Zend_Validate_Abstract
{

    const NOT_ALPHA = 'notAlpha';

    const STRING_EMPTY = 'stringEmpty';

    public $allowWhiteSpace;

    protected static $_filter = null;

    protected $_messageTemplates = array(
        self::NOT_ALPHA    => "'%value%' has not only alphabetic characters",
        self::STRING_EMPTY => "'%value%' is an empty string"
    );

    public function __construct($allowWhiteSpace = false)
    {
        $this->allowWhiteSpace = (boolean) $allowWhiteSpace;
    }

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if ('' === $valueString) {
            $this->_error(self::STRING_EMPTY);
            return false;
        }

        if (null === self::$_filter) {

            require_once 'Zend/Filter/Alpha.php';
            self::$_filter = new Zend_Filter_Alpha();
        }

        self::$_filter->allowWhiteSpace = $this->allowWhiteSpace;

        if ($valueString !== self::$_filter->filter($valueString)) {
            $this->_error(self::NOT_ALPHA);
            return false;
        }

        return true;
    }

}
