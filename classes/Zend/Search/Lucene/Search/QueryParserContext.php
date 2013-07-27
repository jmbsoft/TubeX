<?php


require_once 'Zend/Search/Lucene/FSM.php';

require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryToken.php';

require_once 'Zend/Search/Lucene/Search/Query/Term.php';

require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';

require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';

require_once 'Zend/Search/Lucene/Search/Query/Phrase.php';

require_once 'Zend/Search/Lucene/Search/BooleanExpressionRecognizer.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry.php';

class Zend_Search_Lucene_Search_QueryParserContext
{

    private $_defaultField;

    private $_nextEntryField = null;

    private $_nextEntrySign = null;

    const GM_SIGNS   = 0;  // Signs mode: '+term1 term2 -term3 +(subquery1) -(subquery2)'
    const GM_BOOLEAN = 1;  // Boolean operators mode: 'term1 and term2  or  (subquery1) and not (subquery2)'

    private $_mode = null;

    private $_signs = array();

    private $_entries = array();

    private $_encoding;

    public function __construct($encoding, $defaultField = null)
    {
        $this->_encoding     = $encoding;
        $this->_defaultField = $defaultField;
    }

    public function getField()
    {
        return ($this->_nextEntryField !== null)  ?  $this->_nextEntryField : $this->_defaultField;
    }

    public function setNextEntryField($field)
    {
        $this->_nextEntryField = $field;
    }

    public function setNextEntrySign($sign)
    {
        if ($this->_mode === self::GM_BOOLEAN) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('It\'s not allowed to mix boolean and signs styles in the same subquery.');
        }

        $this->_mode = self::GM_SIGNS;

        if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED) {
            $this->_nextEntrySign = true;
        } else if ($sign == Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED) {
            $this->_nextEntrySign = false;
        } else {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Unrecognized sign type.');
        }
    }

    public function addEntry(Zend_Search_Lucene_Search_QueryEntry $entry)
    {
        if ($this->_mode !== self::GM_BOOLEAN) {
            $this->_signs[] = $this->_nextEntrySign;
        }

        $this->_entries[] = $entry;

        $this->_nextEntryField = null;
        $this->_nextEntrySign  = null;
    }

    public function processFuzzyProximityModifier($parameter = null)
    {
        // Check, that modifier has came just after word or phrase
        if ($this->_nextEntryField !== null  ||  $this->_nextEntrySign !== null) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry = array_pop($this->_entries);

        if (!$lastEntry instanceof Zend_Search_Lucene_Search_QueryEntry) {
            // there are no entries or last entry is boolean operator
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' modifier must follow word or phrase.');
        }

        $lastEntry->processFuzzyProximityModifier($parameter);

        $this->_entries[] = $lastEntry;
    }

    public function boost($boostFactor)
    {
        // Check, that modifier has came just after word or phrase
        if ($this->_nextEntryField !== null  ||  $this->_nextEntrySign !== null) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'^\' modifier must follow word, phrase or subquery.');
        }

        $lastEntry = array_pop($this->_entries);

        if (!$lastEntry instanceof Zend_Search_Lucene_Search_QueryEntry) {
            // there are no entries or last entry is boolean operator
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('\'^\' modifier must follow word, phrase or subquery.');
        }

        $lastEntry->boost($boostFactor);

        $this->_entries[] = $lastEntry;
    }

    public function addLogicalOperator($operator)
    {
        if ($this->_mode === self::GM_SIGNS) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('It\'s not allowed to mix boolean and signs styles in the same subquery.');
        }

        $this->_mode = self::GM_BOOLEAN;

        $this->_entries[] = $operator;
    }

    public function _signStyleExpressionQuery()
    {
        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        if (Zend_Search_Lucene_Search_QueryParser::getDefaultOperator() == Zend_Search_Lucene_Search_QueryParser::B_AND) {
            $defaultSign = true; // required
        } else {
            // Zend_Search_Lucene_Search_QueryParser::B_OR
            $defaultSign = null; // optional
        }

        foreach ($this->_entries as $entryId => $entry) {
            $sign = ($this->_signs[$entryId] !== null) ?  $this->_signs[$entryId] : $defaultSign;
            $query->addSubquery($entry->getQuery($this->_encoding), $sign);
        }

        return $query;
    }

    private function _booleanExpressionQuery()
    {


        $expressionRecognizer = new Zend_Search_Lucene_Search_BooleanExpressionRecognizer();

        require_once 'Zend/Search/Lucene/Exception.php';
        try {
            foreach ($this->_entries as $entry) {
                if ($entry instanceof Zend_Search_Lucene_Search_QueryEntry) {
                    $expressionRecognizer->processLiteral($entry);
                } else {
                    switch ($entry) {
                        case Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_AND_OPERATOR);
                            break;

                        case Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_OR_OPERATOR);
                            break;

                        case Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME:
                            $expressionRecognizer->processOperator(Zend_Search_Lucene_Search_BooleanExpressionRecognizer::IN_NOT_OPERATOR);
                            break;

                        default:
                            throw new Zend_Search_Lucene('Boolean expression error. Unknown operator type.');
                    }
                }
            }

            $conjuctions = $expressionRecognizer->finishExpression();
        } catch (Zend_Search_Exception $e) {
            // throw new Zend_Search_Lucene_Search_QueryParserException('Boolean expression error. Error message: \'' .
            //                                                          $e->getMessage() . '\'.' );
            // It's query syntax error message and it should be user friendly. So FSM message is omitted
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Boolean expression error.');
        }

        // Remove 'only negative' conjunctions
        foreach ($conjuctions as $conjuctionId => $conjuction) {
            $nonNegativeEntryFound = false;

            foreach ($conjuction as $conjuctionEntry) {
                if ($conjuctionEntry[1]) {
                    $nonNegativeEntryFound = true;
                    break;
                }
            }

            if (!$nonNegativeEntryFound) {
                unset($conjuctions[$conjuctionId]);
            }
        }


        $subqueries = array();
        foreach ($conjuctions as  $conjuction) {
            // Check, if it's a one term conjuction
            if (count($conjuction) == 1) {
                $subqueries[] = $conjuction[0][0]->getQuery($this->_encoding);
            } else {
                $subquery = new Zend_Search_Lucene_Search_Query_Boolean();

                foreach ($conjuction as $conjuctionEntry) {
                    $subquery->addSubquery($conjuctionEntry[0]->getQuery($this->_encoding), $conjuctionEntry[1]);
                }

                $subqueries[] = $subquery;
            }
        }

        if (count($subqueries) == 0) {
            return new Zend_Search_Lucene_Search_Query_Insignificant();
        }

        if (count($subqueries) == 1) {
            return $subqueries[0];
        }


        $query = new Zend_Search_Lucene_Search_Query_Boolean();

        foreach ($subqueries as $subquery) {
            // Non-requirered entry/subquery
            $query->addSubquery($subquery);
        }

        return $query;
    }

    public function getQuery()
    {
        if ($this->_mode === self::GM_BOOLEAN) {
            return $this->_booleanExpressionQuery();
        } else {
            return $this->_signStyleExpressionQuery();
        }
    }
}
