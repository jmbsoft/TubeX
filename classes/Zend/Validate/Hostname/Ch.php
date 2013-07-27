<?php


require_once 'Zend/Validate/Hostname/Interface.php';

class Zend_Validate_Hostname_Ch implements Zend_Validate_Hostname_Interface
{

    static function getCharacters()
    {
        return '\x{00EO}-\x{00F6}\x{00F8}-\x{00FF}\x{0153}';
    }

}