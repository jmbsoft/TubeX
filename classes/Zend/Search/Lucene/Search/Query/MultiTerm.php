<?php


require_once 'Zend/Search/Lucene/Search/Query.php';

require_once 'Zend/Search/Lucene/Search/Weight/MultiTerm.php';

class Zend_Search_Lucene_Search_Query_MultiTerm extends Zend_Search_Lucene_Search_Query
{

    private $_terms = array();

    private $_signs;

    private $_resVector = null;

    private $_termsFreqs = array();

    private $_coord = null;

    private $_weights = array();

    public function __construct($terms = null, $signs = null)
    {
        if (is_array($terms)) {
            if (count($terms) > Zend_search_lucene::getTermsPerQueryLimit()) {
                throw new Zend_Search_Lucene_Exception('Terms per query limit is reached.');
            }

            $this->_terms = $terms;

            $this->_signs = null;
            // Check if all terms are required
            if (is_array($signs)) {
                foreach ($signs as $sign ) {
                    if ($sign !== true) {
                        $this->_signs = $signs;
                        break;
                    }
                }
            }
        }
    }

    public function addTerm(Zend_Search_Lucene_Index_Term $term, $sign = null) {
        if ($sign !== true || $this->_signs !== null) {       // Skip, if all terms are required
            if ($this->_signs === null) {                     // Check, If all previous terms are required
                $this->_signs = array();
                foreach ($this->_terms as $prevTerm) {
                    $this->_signs[] = true;
                }
            }
            $this->_signs[] = $sign;
        }

        $this->_terms[] = $term;
    }

    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        if (count($this->_terms) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        }

        // Check, that all fields are qualified
        $allQualified = true;
        foreach ($this->_terms as $term) {
            if ($term->field === null) {
                $allQualified = false;
                break;
            }
        }

        if ($allQualified) {
            return $this;
        } else {

            $query = new Zend_Search_Lucene_Search_Query_Boolean();
            $query->setBoost($this->getBoost());

            foreach ($this->_terms as $termId => $term) {
                $subquery = new Zend_Search_Lucene_Search_Query_Term($term);

                $query->addSubquery($subquery->rewrite($index),
                                    ($this->_signs === null)?  true : $this->_signs[$termId]);
            }

            return $query;
        }
    }

    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        $terms = $this->_terms;
        $signs = $this->_signs;

        foreach ($terms as $id => $term) {
            if (!$index->hasTerm($term)) {
                if ($signs === null  ||  $signs[$id] === true) {
                    // Term is required
                    return new Zend_Search_Lucene_Search_Query_Empty();
                } else {
                    // Term is optional or prohibited
                    // Remove it from terms and signs list
                    unset($terms[$id]);
                    unset($signs[$id]);
                }
            }
        }

        // Check if all presented terms are prohibited
        $allProhibited = true;
        if ($signs === null) {
            $allProhibited = false;
        } else {
            foreach ($signs as $sign) {
                if ($sign !== false) {
                    $allProhibited = false;
                    break;
                }
            }
        }
        if ($allProhibited) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        }


        if (count($terms) == 1) {
            // It's already checked, that it's not a prohibited term

            // It's one term query with one required or optional element
            $optimizedQuery = new Zend_Search_Lucene_Search_Query_Term(reset($terms));
            $optimizedQuery->setBoost($this->getBoost());

            return $optimizedQuery;
        }

        if (count($terms) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        }

        $optimizedQuery = new Zend_Search_Lucene_Search_Query_MultiTerm($terms, $signs);
        $optimizedQuery->setBoost($this->getBoost());
        return $optimizedQuery;
    }

    public function getTerms()
    {
        return $this->_terms;
    }

    public function getSigns()
    {
        return $this->_signs;
    }

    public function setWeight($num, $weight)
    {
        $this->_weights[$num] = $weight;
    }

    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        $this->_weight = new Zend_Search_Lucene_Search_Weight_MultiTerm($this, $reader);
        return $this->_weight;
    }

    private function _calculateConjunctionResult(Zend_Search_Lucene_Interface $reader)
    {
        $this->_resVector = null;

        if (count($this->_terms) == 0) {
            $this->_resVector = array();
        }

        // Order terms by selectivity
        $docFreqs = array();
        $ids      = array();
        foreach ($this->_terms as $id => $term) {
            $docFreqs[] = $reader->docFreq($term);
            $ids[]      = $id; // Used to keep original order for terms with the same selectivity and omit terms comparison
        }
        array_multisort($docFreqs, SORT_ASC, SORT_NUMERIC,
                        $ids,      SORT_ASC, SORT_NUMERIC,
                        $this->_terms);

        $docsFilter = new Zend_Search_Lucene_Index_DocsFilter();
        foreach ($this->_terms as $termId => $term) {
            $termDocs = $reader->termDocs($term, $docsFilter);
        }
        // Treat last retrieved docs vector as a result set
        // (filter collects data for other terms)
        $this->_resVector = array_flip($termDocs);

        foreach ($this->_terms as $termId => $term) {
            $this->_termsFreqs[$termId] = $reader->termFreqs($term, $docsFilter);
        }

        // ksort($this->_resVector, SORT_NUMERIC);
        // Docs are returned ordered. Used algorithms doesn't change elements order.
    }

    private function _calculateNonConjunctionResult(Zend_Search_Lucene_Interface $reader)
    {
        $requiredVectors      = array();
        $requiredVectorsSizes = array();
        $requiredVectorsIds   = array(); // is used to prevent arrays comparison

        $optional   = array();
        $prohibited = array();

        foreach ($this->_terms as $termId => $term) {
            $termDocs = array_flip($reader->termDocs($term));

            if ($this->_signs[$termId] === true) {
                // required
                $requiredVectors[]      = $termDocs;
                $requiredVectorsSizes[] = count($termDocs);
                $requiredVectorsIds[]   = $termId;
            } elseif ($this->_signs[$termId] === false) {
                // prohibited
                // array union
                $prohibited += $termDocs;
            } else {
                // neither required, nor prohibited
                // array union
                $optional += $termDocs;
            }

            $this->_termsFreqs[$termId] = $reader->termFreqs($term);
        }

        // sort resvectors in order of subquery cardinality increasing
        array_multisort($requiredVectorsSizes, SORT_ASC, SORT_NUMERIC,
                        $requiredVectorsIds,   SORT_ASC, SORT_NUMERIC,
                        $requiredVectors);

        $required = null;
        foreach ($requiredVectors as $nextResVector) {
            if($required === null) {
                $required = $nextResVector;
            } else {
                //$required = array_intersect_key($required, $nextResVector);

                $updatedVector = array();
                foreach ($required as $id => $value) {
                    if (isset($nextResVector[$id])) {
                        $updatedVector[$id] = $value;
                    }
                }
                $required = $updatedVector;
            }

            if (count($required) == 0) {
                // Empty result set, we don't need to check other terms
                break;
            }
        }

        if ($required !== null) {
            $this->_resVector = $required;
        } else {
            $this->_resVector = $optional;
        }

        if (count($prohibited) != 0) {
            // $this->_resVector = array_diff_key($this->_resVector, $prohibited);

            if (count($this->_resVector) < count($prohibited)) {
                $updatedVector = $this->_resVector;
                foreach ($this->_resVector as $id => $value) {
                    if (isset($prohibited[$id])) {
                        unset($updatedVector[$id]);
                    }
                }
                $this->_resVector = $updatedVector;
            } else {
                $updatedVector = $this->_resVector;
                foreach ($prohibited as $id => $value) {
                    unset($updatedVector[$id]);
                }
                $this->_resVector = $updatedVector;
            }
        }

        ksort($this->_resVector, SORT_NUMERIC);
    }

    public function _conjunctionScore($docId, Zend_Search_Lucene_Interface $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = $reader->getSimilarity()->coord(count($this->_terms),
                                                            count($this->_terms) );
        }

        $score = 0.0;

        foreach ($this->_terms as $termId => $term) {

            $score += $reader->getSimilarity()->tf($this->_termsFreqs[$termId][$docId]) *
                      $this->_weights[$termId]->getValue() *
                      $reader->norm($docId, $term->field);
        }

        return $score * $this->_coord * $this->getBoost();
    }

    public function _nonConjunctionScore($docId, $reader)
    {
        if ($this->_coord === null) {
            $this->_coord = array();

            $maxCoord = 0;
            foreach ($this->_signs as $sign) {
                if ($sign !== false /* not prohibited */) {
                    $maxCoord++;
                }
            }

            for ($count = 0; $count <= $maxCoord; $count++) {
                $this->_coord[$count] = $reader->getSimilarity()->coord($count, $maxCoord);
            }
        }

        $score = 0.0;
        $matchedTerms = 0;
        foreach ($this->_terms as $termId=>$term) {
            // Check if term is
            if ($this->_signs[$termId] !== false &&        // not prohibited
                isset($this->_termsFreqs[$termId][$docId]) // matched
               ) {
                $matchedTerms++;

                $score +=
                      $reader->getSimilarity()->tf($this->_termsFreqs[$termId][$docId]) *
                      $this->_weights[$termId]->getValue() *
                      $reader->norm($docId, $term->field);
            }
        }

        return $score * $this->_coord[$matchedTerms] * $this->getBoost();
    }

    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        if ($this->_signs === null) {
            $this->_calculateConjunctionResult($reader);
        } else {
            $this->_calculateNonConjunctionResult($reader);
        }

        // Initialize weight if it's not done yet
        $this->_initWeight($reader);
    }

    public function matchedDocs()
    {
        return $this->_resVector;
    }

    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        if (isset($this->_resVector[$docId])) {
            if ($this->_signs === null) {
                return $this->_conjunctionScore($docId, $reader);
            } else {
                return $this->_nonConjunctionScore($docId, $reader);
            }
        } else {
            return 0;
        }
    }

    public function getQueryTerms()
    {
        if ($this->_signs === null) {
            return $this->_terms;
        }

        $terms = array();

        foreach ($this->_signs as $id => $sign) {
            if ($sign !== false) {
                $terms[] = $this->_terms[$id];
            }
        }

        return $terms;
    }

    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        $words = array();

        if ($this->_signs === null) {
            foreach ($this->_terms as $term) {
                $words[] = $term->text;
            }
        } else {
            foreach ($this->_signs as $id => $sign) {
                if ($sign !== false) {
                    $words[] = $this->_terms[$id]->text;
                }
            }
        }

        $doc->highlight($words, $this->_getHighlightColor($colorIndex));
    }

    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping

        $query = '';

        foreach ($this->_terms as $id => $term) {
            if ($id != 0) {
                $query .= ' ';
            }

            if ($this->_signs === null || $this->_signs[$id] === true) {
                $query .= '+';
            } else if ($this->_signs[$id] === false) {
                $query .= '-';
            }

            if ($term->field !== null) {
                $query .= $term->field . ':';
            }
            $query .= $term->text;
        }

        if ($this->getBoost() != 1) {
            $query = '(' . $query . ')^' . $this->getBoost();
        }

        return $query;
    }
}

