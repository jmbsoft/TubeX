<?php


require_once 'Zend/Search/Lucene/Analysis/TokenFilter.php';


class Zend_Search_Lucene_Analysis_TokenFilter_LowerCase extends Zend_Search_Lucene_Analysis_TokenFilter
{

    public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken)
    {
        $newToken = new Zend_Search_Lucene_Analysis_Token(
                                     strtolower( $srcToken->getTermText() ),
                                     $srcToken->getStartOffset(),
                                     $srcToken->getEndOffset());

        $newToken->setPositionIncrement($srcToken->getPositionIncrement());

        return $newToken;
    }
}

