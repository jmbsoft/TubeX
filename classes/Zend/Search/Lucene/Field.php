<?php


class Zend_Search_Lucene_Field
{

    public $name;

    public $value;

    public $isStored    = false;

    public $isIndexed   = true;

    public $isTokenized = true;

    public $isBinary    = false;

    public $storeTermVector = false;

    public $boost = 1.0;

    public $encoding;

    public function __construct($name, $value, $encoding, $isStored, $isIndexed, $isTokenized, $isBinary = false)
    {
        $this->name  = $name;
        $this->value = $value;

        if (!$isBinary) {
            $this->encoding    = $encoding;
            $this->isTokenized = $isTokenized;
        } else {
            $this->encoding    = '';
            $this->isTokenized = false;
        }

        $this->isStored  = $isStored;
        $this->isIndexed = $isIndexed;
        $this->isBinary  = $isBinary;

        $this->storeTermVector = false;
        $this->boost           = 1.0;
    }

    public static function keyword($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, true, false);
    }

    public static function unIndexed($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, false, false);
    }

    public static function binary($name, $value)
    {
        return new self($name, $value, '', true, false, false, true);
    }

    public static function text($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, true, true, true);
    }

    public static function unStored($name, $value, $encoding = '')
    {
        return new self($name, $value, $encoding, false, true, true);
    }

    public function getUtf8Value()
    {
        if (strcasecmp($this->encoding, 'utf8' ) == 0  ||
            strcasecmp($this->encoding, 'utf-8') == 0 ) {
                return $this->value;
        } else {
            
            return (PHP_OS != 'AIX') ? iconv($this->encoding, 'UTF-8', $this->value) : iconv('ISO8859-1', 'UTF-8', $this->value);
        }
    }
}

