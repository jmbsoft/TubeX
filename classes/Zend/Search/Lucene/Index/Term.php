<?php


class Zend_Search_Lucene_Index_Term
{

    public $field;

    public $text;

    public function __construct($text, $field = null)
    {
        $this->field = ($field === null)?  Zend_Search_Lucene::getDefaultSearchField() : $field;
        $this->text  = $text;
    }

    public function key()
    {
        return $this->field . chr(0) . $this->text;
    }

    public static function getPrefix($str, $length)
    {
        $prefixBytes = 0;
        $prefixChars = 0;
        while ($prefixBytes < strlen($str)  &&  $prefixChars < $length) {
            $charBytes = 1;
            if ((ord($str[$prefixBytes]) & 0xC0) == 0xC0) {
                $charBytes++;
                if (ord($str[$prefixBytes]) & 0x20 ) {
                    $charBytes++;
                    if (ord($str[$prefixBytes]) & 0x10 ) {
                        $charBytes++;
                    }
                }
            }

            if ($prefixBytes + $charBytes > strlen($str)) {
                // wrong character
                break;
            }

            $prefixChars++;
            $prefixBytes += $charBytes;
        }

        return substr($str, 0, $prefixBytes);
    }

    public static function getLength($str)
    {
        $bytes = 0;
        $chars = 0;
        while ($bytes < strlen($str)) {
            $charBytes = 1;
            if ((ord($str[$bytes]) & 0xC0) == 0xC0) {
                $charBytes++;
                if (ord($str[$bytes]) & 0x20 ) {
                    $charBytes++;
                    if (ord($str[$bytes]) & 0x10 ) {
                        $charBytes++;
                    }
                }
            }

            if ($bytes + $charBytes > strlen($str)) {
                // wrong character
                break;
            }

            $chars++;
            $bytes += $charBytes;
        }

        return $chars;
    }
}

