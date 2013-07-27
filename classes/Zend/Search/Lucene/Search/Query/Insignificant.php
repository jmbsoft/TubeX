<?php


require_once 'Zend/Search/Lucene/Search/Query.php';

require_once 'Zend/Search/Lucene/Search/Weight/Empty.php';

class Zend_Search_Lucene_Search_Query_Insignificant extends Zend_Search_Lucene_Search_Query
{

    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        return $this;
    }

    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        return $this;
    }

    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        return new Zend_Search_Lucene_Search_Weight_Empty();
    }

    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        // Do nothing
    }

    public function matchedDocs()
    {
        return array();
    }

    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        return 0;
    }

    public function getQueryTerms()
    {
        return array();
    }

    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        // Do nothing
    }

    public function __toString()
    {
        return '<InsignificantQuery>';
    }
}

