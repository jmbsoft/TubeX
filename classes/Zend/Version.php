<?php


final class Zend_Version
{

    const VERSION = '1.7.8';

    public static function compareVersion($version)
    {
        return version_compare($version, self::VERSION);
    }
}

