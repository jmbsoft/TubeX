<?php


require_once 'Zend/Search/Lucene/Search/Weight.php';

class Zend_Search_Lucene_Search_Weight_Term extends Zend_Search_Lucene_Search_Weight
{

    private $_reader;

    private $_term;

    private $_query;

    private $_idf;

    private $_queryWeight;

    public function __construct(Zend_Search_Lucene_Index_Term   $term,
                                Zend_Search_Lucene_Search_Query $query,
                                Zend_Search_Lucene_Interface    $reader)
    {
        $this->_term   = $term;
        $this->_query  = $query;
        $this->_reader = $reader;
    }

    public function sumOfSquaredWeights()
    {
        // compute idf
        $this->_idf = $this->_reader->getSimilarity()->idf($this->_term, $this->_reader);

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

