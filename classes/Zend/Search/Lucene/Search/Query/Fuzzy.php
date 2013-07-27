<?php


require_once 'Zend/Search/Lucene/Search/Query.php';

require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';

class Zend_Search_Lucene_Search_Query_Fuzzy extends Zend_Search_Lucene_Search_Query
{

    const DEFAULT_MIN_SIMILARITY = 0.5;

    const MAX_CLAUSE_COUNT = 1024;

    private $_maxDistances = array();

    private $_term;

    private $_minimumSimilarity;

    private $_prefixLength;

    private $_matches = null;

    private $_scores = null;

    private $_termKeys = null;

    private static $_defaultPrefixLength = 3;

    public function __construct(Zend_Search_Lucene_Index_Term $term, $minimumSimilarity = self::DEFAULT_MIN_SIMILARITY, $prefixLength = null)
    {
        if ($minimumSimilarity < 0) {
            throw new Zend_Search_Lucene_Exception('minimumSimilarity cannot be less than 0');
        }
        if ($minimumSimilarity >= 1) {
            throw new Zend_Search_Lucene_Exception('minimumSimilarity cannot be greater than or equal to 1');
        }
        if ($prefixLength < 0) {
            throw new Zend_Search_Lucene_Exception('prefixLength cannot be less than 0');
        }

        $this->_term              = $term;
        $this->_minimumSimilarity = $minimumSimilarity;
        $this->_prefixLength      = ($prefixLength !== null)? $prefixLength : self::$_defaultPrefixLength;
    }

    public static function getDefaultPrefixLength()
    {
        return self::$_defaultPrefixLength;
    }

    public static function setDefaultPrefixLength($defaultPrefixLength)
    {
        self::$_defaultPrefixLength = $defaultPrefixLength;
    }

    private function _calculateMaxDistance($prefixLength, $termLength, $length)
    {
        $this->_maxDistances[$length] = (int) ((1 - $this->_minimumSimilarity)*(min($termLength, $length) + $prefixLength));
        return $this->_maxDistances[$length];
    }

    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        $this->_matches  = array();
        $this->_scores   = array();
        $this->_termKeys = array();

        if ($this->_term->field === null) {
            // Search through all fields
            $fields = $index->getFieldNames(true /* indexed fields list */);
        } else {
            $fields = array($this->_term->field);
        }

        $prefix           = Zend_Search_Lucene_Index_Term::getPrefix($this->_term->text, $this->_prefixLength);
        $prefixByteLength = strlen($prefix);
        $prefixUtf8Length = Zend_Search_Lucene_Index_Term::getLength($prefix);

        $termLength       = Zend_Search_Lucene_Index_Term::getLength($this->_term->text);

        $termRest         = substr($this->_term->text, $prefixByteLength);
        // we calculate length of the rest in bytes since levenshtein() is not UTF-8 compatible
        $termRestLength   = strlen($termRest);

        $scaleFactor = 1/(1 - $this->_minimumSimilarity);

        $maxTerms = Zend_search_lucene::getTermsPerQueryLimit();
        foreach ($fields as $field) {
            $index->resetTermsStream();

            if ($prefix != '') {
                $index->skipTo(new Zend_Search_Lucene_Index_Term($prefix, $field));

                while ($index->currentTerm() !== null          &&
                       $index->currentTerm()->field == $field  &&
                       substr($index->currentTerm()->text, 0, $prefixByteLength) == $prefix) {
                    // Calculate similarity
                    $target = substr($index->currentTerm()->text, $prefixByteLength);

                    $maxDistance = isset($this->_maxDistances[strlen($target)])?
                                       $this->_maxDistances[strlen($target)] :
                                       $this->_calculateMaxDistance($prefixUtf8Length, $termRestLength, strlen($target));

                    if ($termRestLength == 0) {
                        // we don't have anything to compare.  That means if we just add
                        // the letters for current term we get the new word
                        $similarity = (($prefixUtf8Length == 0)? 0 : 1 - strlen($target)/$prefixUtf8Length);
                    } else if (strlen($target) == 0) {
                        $similarity = (($prefixUtf8Length == 0)? 0 : 1 - $termRestLength/$prefixUtf8Length);
                    } else if ($maxDistance < abs($termRestLength - strlen($target))){
                        //just adding the characters of term to target or vice-versa results in too many edits
                        //for example "pre" length is 3 and "prefixes" length is 8.  We can see that
                        //given this optimal circumstance, the edit distance cannot be less than 5.
                        //which is 8-3 or more precisesly abs(3-8).
                        //if our maximum edit distance is 4, then we can discard this word
                        //without looking at it.
                        $similarity = 0;
                    } else {
                        $similarity = 1 - levenshtein($termRest, $target)/($prefixUtf8Length + min($termRestLength, strlen($target)));
                    }

                    if ($similarity > $this->_minimumSimilarity) {
                        $this->_matches[]  = $index->currentTerm();
                        $this->_termKeys[] = $index->currentTerm()->key();
                        $this->_scores[]   = ($similarity - $this->_minimumSimilarity)*$scaleFactor;

                        if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                            throw new Zend_Search_Lucene_Exception('Terms per query limit is reached.');
                        }
                    }

                    $index->nextTerm();
                }
            } else {
                $index->skipTo(new Zend_Search_Lucene_Index_Term('', $field));

                while ($index->currentTerm() !== null  &&  $index->currentTerm()->field == $field) {
                    // Calculate similarity
                    $target = $index->currentTerm()->text;

                    $maxDistance = isset($this->_maxDistances[strlen($target)])?
                                       $this->_maxDistances[strlen($target)] :
                                       $this->_calculateMaxDistance(0, $termRestLength, strlen($target));

                    if ($maxDistance < abs($termRestLength - strlen($target))){
                        //just adding the characters of term to target or vice-versa results in too many edits
                        //for example "pre" length is 3 and "prefixes" length is 8.  We can see that
                        //given this optimal circumstance, the edit distance cannot be less than 5.
                        //which is 8-3 or more precisesly abs(3-8).
                        //if our maximum edit distance is 4, then we can discard this word
                        //without looking at it.
                        $similarity = 0;
                    } else {
                        $similarity = 1 - levenshtein($termRest, $target)/min($termRestLength, strlen($target));
                    }

                    if ($similarity > $this->_minimumSimilarity) {
                        $this->_matches[]  = $index->currentTerm();
                        $this->_termKeys[] = $index->currentTerm()->key();
                        $this->_scores[]   = ($similarity - $this->_minimumSimilarity)*$scaleFactor;

                        if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                            throw new Zend_Search_Lucene_Exception('Terms per query limit is reached.');
                        }
                    }

                    $index->nextTerm();
                }
            }

            $index->closeTermsStream();
        }

        if (count($this->_matches) == 0) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        } else if (count($this->_matches) == 1) {
            return new Zend_Search_Lucene_Search_Query_Term(reset($this->_matches));
        } else {
            $rewrittenQuery = new Zend_Search_Lucene_Search_Query_Boolean();

            array_multisort($this->_scores,   SORT_DESC, SORT_NUMERIC,
                            $this->_termKeys, SORT_ASC,  SORT_STRING,
                            $this->_matches);

            $termCount = 0;
            foreach ($this->_matches as $id => $matchedTerm) {
                $subquery = new Zend_Search_Lucene_Search_Query_Term($matchedTerm);
                $subquery->setBoost($this->_scores[$id]);

                $rewrittenQuery->addSubquery($subquery);

                $termCount++;
                if ($termCount >= self::MAX_CLAUSE_COUNT) {
                    break;
                }
            }

            return $rewrittenQuery;
        }
    }

    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        throw new Zend_Search_Lucene_Exception('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    public function getQueryTerms()
    {
        if ($this->_matches === null) {
            throw new Zend_Search_Lucene_Exception('Search has to be performed first to get matched terms');
        }

        return $this->_matches;
    }

    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        throw new Zend_Search_Lucene_Exception('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        throw new Zend_Search_Lucene_Exception('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    public function matchedDocs()
    {
        throw new Zend_Search_Lucene_Exception('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        throw new Zend_Search_Lucene_Exception('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        $words = array();

        foreach ($this->_matches as $term) {
            $words[] = $term->text;
        }

        $doc->highlight($words, $this->_getHighlightColor($colorIndex));
    }

    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
        return (($this->_term->field === null)? '' : $this->_term->field . ':')
             . $this->_term->text . '~'
             . (($this->_minimumSimilarity != self::DEFAULT_MIN_SIMILARITY)? round($this->_minimumSimilarity, 4) : '');
    }
}

