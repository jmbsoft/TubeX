<?php


require_once 'Zend/Search/Lucene/Search/Query.php';

require_once 'Zend/Search/Lucene/Search/Weight/Phrase.php';

class Zend_Search_Lucene_Search_Query_Phrase extends Zend_Search_Lucene_Search_Query
{

    private $_terms;

    private $_offsets;

    private $_slop;

    private $_resVector = null;

    private $_termsPositions = array();

    public function __construct($terms = null, $offsets = null, $field = null)
    {
        $this->_slop = 0;

        if (is_array($terms)) {
            $this->_terms = array();
            foreach ($terms as $termId => $termText) {
                $this->_terms[$termId] = ($field !== null)? new Zend_Search_Lucene_Index_Term($termText, $field):
                                                            new Zend_Search_Lucene_Index_Term($termText);
            }
        } else if ($terms === null) {
            $this->_terms = array();
        } else {
            throw new Zend_Search_Lucene_Exception('terms argument must be array of strings or null');
        }

        if (is_array($offsets)) {
            if (count($this->_terms) != count($offsets)) {
                throw new Zend_Search_Lucene_Exception('terms and offsets arguments must have the same size.');
            }
            $this->_offsets = $offsets;
        } else if ($offsets === null) {
            $this->_offsets = array();
            foreach ($this->_terms as $termId => $term) {
                $position = count($this->_offsets);
                $this->_offsets[$termId] = $position;
            }
        } else {
            throw new Zend_Search_Lucene_Exception('offsets argument must be array of strings or null');
        }
    }

    public function setSlop($slop)
    {
        $this->_slop = $slop;
    }

    public function getSlop()
    {
        return $this->_slop;
    }

    public function addTerm(Zend_Search_Lucene_Index_Term $term, $position = null) {
        if ((count($this->_terms) != 0)&&(end($this->_terms)->field != $term->field)) {
            throw new Zend_Search_Lucene_Exception('All phrase terms must be in the same field: ' .
                                                   $term->field . ':' . $term->text);
        }

        $this->_terms[] = $term;
        if ($position !== null) {
            $this->_offsets[] = $position;
        } else if (count($this->_offsets) != 0) {
            $this->_offsets[] = end($this->_offsets) + 1;
        } else {
            $this->_offsets[] = 0;
        }
    }

    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        if (count($this->_terms) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        } else if ($this->_terms[0]->field !== null) {
            return $this;
        } else {
            $query = new Zend_Search_Lucene_Search_Query_Boolean();
            $query->setBoost($this->getBoost());

            foreach ($index->getFieldNames(true) as $fieldName) {
                $subquery = new Zend_Search_Lucene_Search_Query_Phrase();
                $subquery->setSlop($this->getSlop());

                foreach ($this->_terms as $termId => $term) {
                    $qualifiedTerm = new Zend_Search_Lucene_Index_Term($term->text, $fieldName);

                    $subquery->addTerm($qualifiedTerm, $this->_offsets[$termId]);
                }

                $query->addSubquery($subquery);
            }

            return $query;
        }
    }

    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        // Check, that index contains all phrase terms
        foreach ($this->_terms as $term) {
            if (!$index->hasTerm($term)) {
                return new Zend_Search_Lucene_Search_Query_Empty();
            }
        }

        if (count($this->_terms) == 1) {
            // It's one term query
            $optimizedQuery = new Zend_Search_Lucene_Search_Query_Term(reset($this->_terms));
            $optimizedQuery->setBoost($this->getBoost());

            return $optimizedQuery;
        }

        if (count($this->_terms) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        }


        return $this;
    }

    public function getTerms()
    {
        return $this->_terms;
    }

    public function setWeight($num, $weight)
    {
        $this->_weights[$num] = $weight;
    }

    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        $this->_weight = new Zend_Search_Lucene_Search_Weight_Phrase($this, $reader);
        return $this->_weight;
    }

    public function _exactPhraseFreq($docId)
    {
        $freq = 0;

        // Term Id with lowest cardinality
        $lowCardTermId = null;

        // Calculate $lowCardTermId
        foreach ($this->_terms as $termId => $term) {
            if ($lowCardTermId === null ||
                count($this->_termsPositions[$termId][$docId]) <
                count($this->_termsPositions[$lowCardTermId][$docId]) ) {
                    $lowCardTermId = $termId;
                }
        }

        // Walk through positions of the term with lowest cardinality
        foreach ($this->_termsPositions[$lowCardTermId][$docId] as $lowCardPos) {
            // We expect phrase to be found
            $freq++;

            // Walk through other terms
            foreach ($this->_terms as $termId => $term) {
                if ($termId != $lowCardTermId) {
                    $expectedPosition = $lowCardPos +
                                            ($this->_offsets[$termId] -
                                             $this->_offsets[$lowCardTermId]);

                    if (!in_array($expectedPosition, $this->_termsPositions[$termId][$docId])) {
                        $freq--;  // Phrase wasn't found.
                        break;
                    }
                }
            }
        }

        return $freq;
    }

    public function _sloppyPhraseFreq($docId, Zend_Search_Lucene_Interface $reader)
    {
        $freq = 0;

        $phraseQueue = array();
        $phraseQueue[0] = array(); // empty phrase
        $lastTerm = null;

        // Walk through the terms to create phrases.
        foreach ($this->_terms as $termId => $term) {
            $queueSize = count($phraseQueue);
            $firstPass = true;

            // Walk through the term positions.
            // Each term position produces a set of phrases.
            foreach ($this->_termsPositions[$termId][$docId] as $termPosition ) {
                if ($firstPass) {
                    for ($count = 0; $count < $queueSize; $count++) {
                        $phraseQueue[$count][$termId] = $termPosition;
                    }
                } else {
                    for ($count = 0; $count < $queueSize; $count++) {
                        if ($lastTerm !== null &&
                            abs( $termPosition - $phraseQueue[$count][$lastTerm] -
                                 ($this->_offsets[$termId] - $this->_offsets[$lastTerm])) > $this->_slop) {
                            continue;
                        }

                        $newPhraseId = count($phraseQueue);
                        $phraseQueue[$newPhraseId]          = $phraseQueue[$count];
                        $phraseQueue[$newPhraseId][$termId] = $termPosition;
                    }

                }

                $firstPass = false;
            }
            $lastTerm = $termId;
        }


        foreach ($phraseQueue as $phrasePos) {
            $minDistance = null;

            for ($shift = -$this->_slop; $shift <= $this->_slop; $shift++) {
                $distance = 0;
                $start = reset($phrasePos) - reset($this->_offsets) + $shift;

                foreach ($this->_terms as $termId => $term) {
                    $distance += abs($phrasePos[$termId] - $this->_offsets[$termId] - $start);

                    if($distance > $this->_slop) {
                        break;
                    }
                }

                if ($minDistance === null || $distance < $minDistance) {
                    $minDistance = $distance;
                }
            }

            if ($minDistance <= $this->_slop) {
                $freq += $reader->getSimilarity()->sloppyFreq($minDistance);
            }
        }

        return $freq;
    }

    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        $this->_resVector = null;

        if (count($this->_terms) == 0) {
            $this->_resVector = array();
        }

        $resVectors      = array();
        $resVectorsSizes = array();
        $resVectorsIds   = array(); // is used to prevent arrays comparison
        foreach ($this->_terms as $termId => $term) {
            $resVectors[]      = array_flip($reader->termDocs($term));
            $resVectorsSizes[] = count(end($resVectors));
            $resVectorsIds[]   = $termId;

            $this->_termsPositions[$termId] = $reader->termPositions($term);
        }
        // sort resvectors in order of subquery cardinality increasing
        array_multisort($resVectorsSizes, SORT_ASC, SORT_NUMERIC,
                        $resVectorsIds,   SORT_ASC, SORT_NUMERIC,
                        $resVectors);

        foreach ($resVectors as $nextResVector) {
            if($this->_resVector === null) {
                $this->_resVector = $nextResVector;
            } else {
                //$this->_resVector = array_intersect_key($this->_resVector, $nextResVector);

                $updatedVector = array();
                foreach ($this->_resVector as $id => $value) {
                    if (isset($nextResVector[$id])) {
                        $updatedVector[$id] = $value;
                    }
                }
                $this->_resVector = $updatedVector;
            }

            if (count($this->_resVector) == 0) {
                // Empty result set, we don't need to check other terms
                break;
            }
        }

        // ksort($this->_resVector, SORT_NUMERIC);
        // Docs are returned ordered. Used algorithm doesn't change elements order.

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
            if ($this->_slop == 0) {
                $freq = $this->_exactPhraseFreq($docId);
            } else {
                $freq = $this->_sloppyPhraseFreq($docId, $reader);
            }

            if ($freq != 0) {
                $tf = $reader->getSimilarity()->tf($freq);
                $weight = $this->_weight->getValue();
                $norm = $reader->norm($docId, reset($this->_terms)->field);

                return $tf * $weight * $norm * $this->getBoost();
            }

            // Included in result, but culculated freq is zero
            return 0;
        } else {
            return 0;
        }
    }

    public function getQueryTerms()
    {
        return $this->_terms;
    }

    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        $words = array();
        foreach ($this->_terms as $term) {
            $words[] = $term->text;
        }

        $doc->highlight($words, $this->_getHighlightColor($colorIndex));
    }

    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping

        $query = '';

        if (isset($this->_terms[0]) && $this->_terms[0]->field !== null) {
            $query .= $this->_terms[0]->field . ':';
        }

        $query .= '"';

        foreach ($this->_terms as $id => $term) {
            if ($id != 0) {
                $query .= ' ';
            }
            $query .= $term->text;
        }

        $query .= '"';

        if ($this->_slop != 0) {
            $query .= '~' . $this->_slop;
        }

        return $query;
    }
}

