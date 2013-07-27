<?php


require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

class Zend_Search_Lucene_Search_QueryEntry_Phrase extends Zend_Search_Lucene_Search_QueryEntry
{

    private $_phrase;

    private $_field;

    private $_proximityQuery = false;

    private $_wordsDistance = 0;

    public function __construct($phrase, $field)
    {
        $this->_phrase = $phrase;
        $this->_field  = $field;
    }

    public function processFuzzyProximityModifier($parameter = null)
    {
        $this->_proximityQuery = true;

        if ($parameter !== null) {
            $this->_wordsDistance = $parameter;
        }
    }

    public function getQuery($encoding)
    {
        if (strpos($this->_phrase, '?') !== false || strpos($this->_phrase, '*') !== false) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Wildcards are only allowed in a single terms.');
        }

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_phrase, $encoding);

        if (count($tokens) == 0) {
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($tokens) == 1) {
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->_boost);

            return $query;
        }

        //It's not empty or one term query
        $position = -1;
        $query = new Zend_Search_Lucene_Search_Query_Phrase();
        foreach ($tokens as $token) {
            $position += $token->getPositionIncrement();
            $term = new Zend_Search_Lucene_Index_Term($token->getTermText(), $this->_field);
            $query->addTerm($term, $position);
        }

        if ($this->_proximityQuery) {
            $query->setSlop($this->_wordsDistance);
        }

        $query->setBoost($this->_boost);

        return $query;
    }
}
