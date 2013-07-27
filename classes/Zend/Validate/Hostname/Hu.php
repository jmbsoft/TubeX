<?php


require_once 'Zend/Validate/Hostname/Interface.php';

class Zend_Validate_Hostname_Hu implements Zend_Validate_Hostname_Interface
{

    static function getCharacters()
    {
        return '\x{00E1}\x{00E9}\x{00ED}\x{00F3}\x{00F6}\x{0151}\x{00FA}\x{00FC}\x{0171}';
    }

}