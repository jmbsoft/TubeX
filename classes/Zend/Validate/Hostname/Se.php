<?php


require_once 'Zend/Validate/Hostname/Interface.php';

class Zend_Validate_Hostname_Se implements Zend_Validate_Hostname_Interface
{

    static function getCharacters()
    {
        return '\x{00E5}\x{00E4}\x{00F6}\x{00FC}\x{00E9}';
    }

}