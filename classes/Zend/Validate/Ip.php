<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Ip extends Zend_Validate_Abstract
{
    const NOT_IP_ADDRESS = 'notIpAddress';

    protected $_messageTemplates = array(
        self::NOT_IP_ADDRESS => "'%value%' does not appear to be a valid IP address"
    );

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if ((ip2long($valueString) === false) || (long2ip(ip2long($valueString)) !== $valueString)) {
            if (!function_exists('inet_pton')) {
                $this->_error();
                return false;
            } else if ((@inet_pton($value) === false) ||(inet_ntop(@inet_pton($value)) !== $valueString)) {
                $this->_error();
                return false;
            }
        }

        return true;
    }

}
