<?php


require_once 'Zend/Validate/Hostname/Interface.php';

class Zend_Validate_Hostname_Fi implements Zend_Validate_Hostname_Interface
{

    static function getCharacters()
    {
        return '\x{00E5}\x{00E4}\x{00F6}';
    }

}