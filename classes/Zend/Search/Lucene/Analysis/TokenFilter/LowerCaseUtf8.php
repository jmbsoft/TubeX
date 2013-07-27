<?php


require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


class Zend_Search_Lucene_Analysis_TokenFilter_LowerCaseUtf8 extends Zend_Search_Lucene_Analysis_TokenFilter
{

    public function __construct()
    {
        if (!function_exists('mb_strtolower')) {
            // mbstring extension is disabled
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Utf8 compatible lower case filter needs mbstring extension to be enabled.');
        }
    }

    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        $newToken = new Zend_Search_Lucene_Analysis_Token(
                                     mb_strtolower($srcToken->getTermText(), 'UTF-8'),
                                     $srcToken->getStartOffset(),
                                     $srcToken->getEndOffset());

        $newToken->setPositionIncrement($srcToken->getPositionIncrement());

        return $newToken;
    }
}

