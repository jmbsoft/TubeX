<?php


require_once 'Zend/Search/Lucene/Analysis/Token.php';


abstract class Zend_Search_Lucene_Analysis_TokenFilter
{

    abstract public function normalize(Zend_Search_Lucene_Analysis_Token $srcToken);
}

