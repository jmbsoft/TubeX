<?php


require_once 'Zend/Search/Lucene/Analysis/Analyzer/Common/Utf8.php';

require_once 'Zend/Search/Lucene/Analysis/TokenFilter/LowerCaseUtf8.php';



class Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive extends Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8 
{
    public function __construct()
    {
        parent::__construct();

        $this->addFilter(new Zend_Search_Lucene_Analysis_TokenFilter_LowerCaseUtf8());
    }
}

