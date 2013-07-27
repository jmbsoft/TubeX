<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_InArray extends Zend_Validate_Abstract
{

    const NOT_IN_ARRAY = 'notInArray';

    protected $_messageTemplates = array(
        self::NOT_IN_ARRAY => "'%value%' was not found in the haystack"
    );

    protected $_haystack;

    protected $_strict;

    public function __construct(array $haystack, $strict = false)
    {
        $this->setHaystack($haystack)
             ->setStrict($strict);
    }

    public function getHaystack()
    {
        return $this->_haystack;
    }

    public function setHaystack(array $haystack)
    {
        $this->_haystack = $haystack;
        return $this;
    }

    public function getStrict()
    {
        return $this->_strict;
    }

    public function setStrict($strict)
    {
        $this->_strict = $strict;
        return $this;
    }

    public function isValid($value)
    {
        $this->_setValue($value);
        if (!in_array($value, $this->_haystack, $this->_strict)) {
            $this->_error();
            return false;
        }
        return true;
    }

}
