<?php


require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


class Zend_Search_Lucene_Analysis_TokenFilter_ShortWords extends Zend_Search_Lucene_Analysis_TokenFilter
{

    private $length;

    public function __construct($length = 2) {
        $this->length = $length;
    }

    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken) {
        if (strlen($srcToken->getTermText()) < $this->length) {
            return null;
        } else {
            return $srcToken;
        }
    }
}

