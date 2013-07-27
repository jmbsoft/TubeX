<?php


require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum.php';

require_once 'Zend/Search/Lucene/Analysis/TokenFilter/LowerCase.php';



class Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive extends Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum
{
    public function __construct()
    {
        $this->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_LowerCase());
    }
}

