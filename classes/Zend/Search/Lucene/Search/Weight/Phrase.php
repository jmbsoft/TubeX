<?php


require_once 'Zend/Search/Lucene/Search/Weight.php';

class Zend_Search_Lucene_Search_Weight_Phrase extends Zend_Search_Lucene_Search_Weight
{

    private $_reader;

    private $_query;

    private $_idf;

    public function __construct(Zend_Search_Lucene_Search_Query_Phrase $query,
                                Zend_Search_Lucene_Interface           $reader)
    {
        $this->_query  = $query;
        $this->_reader = $reader;
    }

    public function sumOfSquaredWeights()
    {
        // compute idf
        $this->_idf = $this->_reader->getSimilarity()->idf($this->_query->getTerms(), $this->_reader);

        // compute query weight
        $this->_queryWeight = $this->_idf * $this->_query->getBoost();

        // square it
        return $this->_queryWeight * $this->_queryWeight;
    }

    public function normalize($queryNorm)
    {
        $this->_queryNorm = $queryNorm;

        // normalize query weight
        $this->_queryWeight *= $queryNorm;

        // idf for documents
        $this->_value = $this->_queryWeight * $this->_idf;
    }
}


