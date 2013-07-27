<?php


require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry.php';

require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

class Zend_Search_Lucene_Search_QueryEntry_Term extends Zend_Search_Lucene_Search_QueryEntry
{

    private $_term;

    private $_field;

    private $_fuzzyQuery = false;

    private $_similarity = 1.;

    public function __construct($term, $field)
    {
        $this->_term  = $term;
        $this->_field = $field;
    }

    public function processFuzzyProximityModifier($parameter = null)
    {
        $this->_fuzzyQuery = true;

        if ($parameter !== null) {
            $this->_similarity = $parameter;
        } else {
            $this->_similarity = Zend_Search_Lucene_Search_Query_Fuzzy::DEFAULT_MIN_SIMILARITY;
        }
    }

    public function getQuery($encoding)
    {
        if (strpos($this->_term, '?') !== false || strpos($this->_term, '*') !== false) {
            if ($this->_fuzzyQuery) {
                require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
                throw new Zend_Search_Lucene_Search_QueryParserException('Fuzzy search is not supported for terms with wildcards.');
            }

            $pattern = '';

            $subPatterns = explode('*', $this->_term);

            $astericFirstPass = true;
            foreach ($subPatterns as $subPattern) {
                if (!$astericFirstPass) {
                    $pattern .= '*';
                } else {
                    $astericFirstPass = false;
                }

                $subPatternsL2 = explode('?', $subPattern);

                $qMarkFirstPass = true;
                foreach ($subPatternsL2 as $subPatternL2) {
                    if (!$qMarkFirstPass) {
                        $pattern .= '?';
                    } else {
                        $qMarkFirstPass = false;
                    }

                    $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($subPatternL2, $encoding);
                    if (count($tokens) > 1) {
                        require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
                        throw new Zend_Search_Lucene_Search_QueryParserException('Wildcard search is supported only for non-multiple word terms');
                    }

                    foreach ($tokens as $token) {
                        $pattern .= $token->getTermText();
                    }
                }
            }

            $term  = new Zend_Search_Lucene_Index_Term($pattern, $this->_field);
            $query = new Zend_Search_Lucene_Search_Query_Wildcard($term);
            $query->setBoost($this->_boost);

            return $query;
        }

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_term, $encoding);

        if (count($tokens) == 0) {
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($tokens) == 1  && !$this->_fuzzyQuery) {
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            $query = new Zend_Search_Lucene_Search_Query_Term($term);
            $query->setBoost($this->_boost);

            return $query;
        }

        if (count($tokens) == 1  && $this->_fuzzyQuery) {
            $term  = new Zend_Search_Lucene_Index_Term($tokens[0]->getTermText(), $this->_field);
            $query = new Zend_Search_Lucene_Search_Query_Fuzzy($term, $this->_similarity);
            $query->setBoost($this->_boost);

            return $query;
        }

        if ($this->_fuzzyQuery) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Fuzzy search is supported only for non-multiple word terms');
        }

        //It's not empty or one term query
        $query = new Zend_Search_Lucene_Search_Query_MultiTerm();

        foreach ($tokens as $token) {
            $term = new Zend_Search_Lucene_Index_Term($token->getTermText(), $this->_field);
            $query->addTerm($term, true); // all subterms are required
        }

        $query->setBoost($this->_boost);

        return $query;
    }
}
