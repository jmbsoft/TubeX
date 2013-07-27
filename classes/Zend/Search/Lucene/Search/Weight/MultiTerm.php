<?php


require_once 'Zend/Search/Lucene/Search/Weight.php';

class Zend_Search_Lucene_Search_Weight_MultiTerm extends Zend_Search_Lucene_Search_Weight
{

    private $_reader;

    private $_query;

    private $_weights;

    public function __construct(Zend_Search_Lucene_Search_Query $query,
                                Zend_Search_Lucene_Interface    $reader)
    {
        $this->_query   = $query;
        $this->_reader  = $reader;
        $this->_weights = array();

        $signs = $query->getSigns();

        foreach ($query->getTerms() as $id => $term) {
            if ($signs === null || $signs[$id] === null || $signs[$id]) {
                $this->_weights[$id] = new Zend_Search_Lucene_Search_Weight_Term($term, $query, $reader);
                $query->setWeight($id, $this->_weights[$id]);
            }
        }
    }

    public function getValue()
    {
        return $this->_query->getBoost();
    }

    public function sumOfSquaredWeights()
    {
        $sum = 0;
        foreach ($this->_weights as $weight) {
            // sum sub weights
            $sum += $weight->sumOfSquaredWeights();
        }

        // boost each sub-weight
        $sum *= $this->_query->getBoost() * $this->_query->getBoost();

        // check for empty query (like '-something -another')
        if ($sum == 0) {
            $sum = 1.0;
        }
        return $sum;
    }

    public function normalize($queryNorm)
    {
        // incorporate boost
        $queryNorm *= $this->_query->getBoost();

        foreach ($this->_weights as $weight) {
            $weight->normalize($queryNorm);
        }
    }
}


