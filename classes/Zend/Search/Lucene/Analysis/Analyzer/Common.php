<?php


require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

abstract class Zend_Search_Lucene_Analysis_Analyzer_Common extends Zend_Search_Lucene_Analysis_Analyzer
{

    private $_filters = array();

    public function addFilter(Zend_Search_Lucene_Analysis_TokenFilter $filter)
    {
        $this->_filters[] = $filter;
    }

    public function normalize(Zend_Search_Lucene_Analysis_Token $token)
    {
        foreach ($this->_filters as $filter) {
            $token = $filter->normalize($token);

            // resulting token can be null if the filter removes it
            if (is_null($token)) {
                return null;
            }
        }

        return $token;
    }
}

