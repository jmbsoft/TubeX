<?php


require_once 'Zend/Validate/Hostname/Interface.php';

class Zend_Validate_Hostname_No implements Zend_Validate_Hostname_Interface
{

    static function getCharacters()
    {
        return  '\x00E1\x00E0\x00E4\x010D\x00E7\x0111\x00E9\x00E8\x00EA\x\x014B' .
                '\x0144\x00F1\x00F3\x00F2\x00F4\x00F6\x0161\x0167\x00FC\x017E\x00E6' .
                '\x00F8\x00E5';
    }

}
