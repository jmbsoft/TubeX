<?php


require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/Query/Term.php';

require_once 'Zend/Search/Lucene/Search/Query/MultiTerm.php';

require_once 'Zend/Search/Lucene/Search/Query/Boolean.php';

require_once 'Zend/Search/Lucene/Search/Query/Phrase.php';

require_once 'Zend/Search/Lucene/Search/Query/Wildcard.php';

require_once 'Zend/Search/Lucene/Search/Query/Range.php';

require_once 'Zend/Search/Lucene/Search/Query/Fuzzy.php';

require_once 'Zend/Search/Lucene/Search/Query/Empty.php';

require_once 'Zend/Search/Lucene/Search/Query/Insignificant.php';

require_once 'Zend/Search/Lucene/Search/QueryLexer.php';

require_once 'Zend/Search/Lucene/Search/QueryParserContext.php';

require_once 'Zend/Search/Lucene/FSM.php';

class Zend_Search_Lucene_Search_QueryParser extends Zend_Search_Lucene_FSM
{

    private static $_instance = null;

    private $_lexer;

    private $_tokens;

    private $_currentToken;

    private $_lastToken = null;

    private $_rqFirstTerm = null;

    private $_context;

    private $_contextStack;

    private $_encoding;

    private $_defaultEncoding = '';

    private $_suppressQueryParsingExceptions = true;

    const B_OR  = 0;
    const B_AND = 1;

    private $_defaultOperator = self::B_OR;

    const ST_COMMON_QUERY_ELEMENT       = 0;   // Terms, phrases, operators
    const ST_CLOSEDINT_RQ_START         = 1;   // Range query start (closed interval) - '['
    const ST_CLOSEDINT_RQ_FIRST_TERM    = 2;   // First term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_TO_TERM       = 3;   // 'TO' lexeme in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_LAST_TERM     = 4;   // Second term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_END           = 5;   // Range query end (closed interval) - ']'
    const ST_OPENEDINT_RQ_START         = 6;   // Range query start (opened interval) - '{'
    const ST_OPENEDINT_RQ_FIRST_TERM    = 7;   // First term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_TO_TERM       = 8;   // 'TO' lexeme in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_LAST_TERM     = 9;   // Second term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_END           = 10;  // Range query end (opened interval) - '}'

    public function __construct()
    {
        parent::__construct(array(self::ST_COMMON_QUERY_ELEMENT,
                                  self::ST_CLOSEDINT_RQ_START,
                                  self::ST_CLOSEDINT_RQ_FIRST_TERM,
                                  self::ST_CLOSEDINT_RQ_TO_TERM,
                                  self::ST_CLOSEDINT_RQ_LAST_TERM,
                                  self::ST_CLOSEDINT_RQ_END,
                                  self::ST_OPENEDINT_RQ_START,
                                  self::ST_OPENEDINT_RQ_FIRST_TERM,
                                  self::ST_OPENEDINT_RQ_TO_TERM,
                                  self::ST_OPENEDINT_RQ_LAST_TERM,
                                  self::ST_OPENEDINT_RQ_END
                                 ),
                            Zend_Search_Lucene_Search_QueryToken::getTypes());

        $this->addRules(
             array(array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_WORD,             self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,           self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,            self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,         self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK,  self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK,    self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,   self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_END,     self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,        self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NUMBER,           self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_CLOSEDINT_RQ_START,      Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_FIRST_TERM),
                   array(self::ST_CLOSEDINT_RQ_FIRST_TERM, Zend_Search_Lucene_Search_QueryToken::TT_TO_LEXEME,      self::ST_CLOSEDINT_RQ_TO_TERM),
                   array(self::ST_CLOSEDINT_RQ_TO_TERM,    Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_LAST_TERM),
                   array(self::ST_CLOSEDINT_RQ_LAST_TERM,  Zend_Search_Lucene_Search_QueryToken::TT_RANGE_INCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_OPENEDINT_RQ_START,      Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_FIRST_TERM),
                   array(self::ST_OPENEDINT_RQ_FIRST_TERM, Zend_Search_Lucene_Search_QueryToken::TT_TO_LEXEME,      self::ST_OPENEDINT_RQ_TO_TERM),
                   array(self::ST_OPENEDINT_RQ_TO_TERM,    Zend_Search_Lucene_Search_QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_LAST_TERM),
                   array(self::ST_OPENEDINT_RQ_LAST_TERM,  Zend_Search_Lucene_Search_QueryToken::TT_RANGE_EXCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));



        $addTermEntryAction             = new Zend_Search_Lucene_FSMAction($this, 'addTermEntry');
        $addPhraseEntryAction           = new Zend_Search_Lucene_FSMAction($this, 'addPhraseEntry');
        $setFieldAction                 = new Zend_Search_Lucene_FSMAction($this, 'setField');
        $setSignAction                  = new Zend_Search_Lucene_FSMAction($this, 'setSign');
        $setFuzzyProxAction             = new Zend_Search_Lucene_FSMAction($this, 'processFuzzyProximityModifier');
        $processModifierParameterAction = new Zend_Search_Lucene_FSMAction($this, 'processModifierParameter');
        $subqueryStartAction            = new Zend_Search_Lucene_FSMAction($this, 'subqueryStart');
        $subqueryEndAction              = new Zend_Search_Lucene_FSMAction($this, 'subqueryEnd');
        $logicalOperatorAction          = new Zend_Search_Lucene_FSMAction($this, 'logicalOperator');
        $openedRQFirstTermAction        = new Zend_Search_Lucene_FSMAction($this, 'openedRQFirstTerm');
        $openedRQLastTermAction         = new Zend_Search_Lucene_FSMAction($this, 'openedRQLastTerm');
        $closedRQFirstTermAction        = new Zend_Search_Lucene_FSMAction($this, 'closedRQFirstTerm');
        $closedRQLastTermAction         = new Zend_Search_Lucene_FSMAction($this, 'closedRQLastTerm');


        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_WORD,            $addTermEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PHRASE,          $addPhraseEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FIELD,           $setFieldAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_REQUIRED,        $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_PROHIBITED,      $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK, $setFuzzyProxAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NUMBER,          $processModifierParameterAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_START,  $subqueryStartAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_SUBQUERY_END,    $subqueryEndAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_AND_LEXEME,      $logicalOperatorAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_OR_LEXEME,       $logicalOperatorAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, Zend_Search_Lucene_Search_QueryToken::TT_NOT_LEXEME,      $logicalOperatorAction);

        $this->addEntryAction(self::ST_OPENEDINT_RQ_FIRST_TERM, $openedRQFirstTermAction);
        $this->addEntryAction(self::ST_OPENEDINT_RQ_LAST_TERM,  $openedRQLastTermAction);
        $this->addEntryAction(self::ST_CLOSEDINT_RQ_FIRST_TERM, $closedRQFirstTermAction);
        $this->addEntryAction(self::ST_CLOSEDINT_RQ_LAST_TERM,  $closedRQLastTermAction);



        $this->_lexer = new Zend_Search_Lucene_Search_QueryLexer();
    }

    private static function _getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function setDefaultEncoding($encoding)
    {
        self::_getInstance()->_defaultEncoding = $encoding;
    }

    public static function getDefaultEncoding()
    {
       return self::_getInstance()->_defaultEncoding;
    }

    public static function setDefaultOperator($operator)
    {
        self::_getInstance()->_defaultOperator = $operator;
    }

    public static function getDefaultOperator()
    {
        return self::_getInstance()->_defaultOperator;
    }

    public static function suppressQueryParsingExceptions()
    {
        self::_getInstance()->_suppressQueryParsingExceptions = true;
    }

    public static function dontSuppressQueryParsingExceptions()
    {
        self::_getInstance()->_suppressQueryParsingExceptions = false;
    }

    public static function queryParsingExceptionsSuppressed()
    {
        return self::_getInstance()->_suppressQueryParsingExceptions;
    }

    public static function parse($strQuery, $encoding = null)
    {
        self::_getInstance();

        // Reset FSM if previous parse operation didn't return it into a correct state
        self::$_instance->reset();

        require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
        try {
            self::$_instance->_encoding     = ($encoding !== null) ? $encoding : self::$_instance->_defaultEncoding;
            self::$_instance->_lastToken    = null;
            self::$_instance->_context      = new Zend_Search_Lucene_Search_QueryParserContext(self::$_instance->_encoding);
            self::$_instance->_contextStack = array();
            self::$_instance->_tokens       = self::$_instance->_lexer->tokenize($strQuery, self::$_instance->_encoding);

            // Empty query
            if (count(self::$_instance->_tokens) == 0) {
                return new Zend_Search_Lucene_Search_Query_Insignificant();
            }


            foreach (self::$_instance->_tokens as $token) {
                try {
                    self::$_instance->_currentToken = $token;
                    self::$_instance->process($token->type);

                    self::$_instance->_lastToken = $token;
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'There is no any rule for') !== false) {
                        throw new Zend_Search_Lucene_Search_QueryParserException( 'Syntax error at char position ' . $token->position . '.' );
                    }

                    throw $e;
                }
            }

            if (count(self::$_instance->_contextStack) != 0) {
                throw new Zend_Search_Lucene_Search_QueryParserException('Syntax Error: mismatched parentheses, every opening must have closing.' );
            }

            return self::$_instance->_context->getQuery();
        } catch (Zend_Search_Lucene_Search_QueryParserException $e) {
            if (self::$_instance->_suppressQueryParsingExceptions) {
                $queryTokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($strQuery, self::$_instance->_encoding);

                $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
                $termsSign = (self::$_instance->_defaultOperator == self::B_AND) ? true /* required term */ :
                                                                                   null /* optional term */;

                foreach ($queryTokens as $token) {
                    $query->addTerm(new Zend_Search_Lucene_Index_Term($token->getTermText()), $termsSign);
                }


                return $query;
            } else {
                throw $e;
            }
        }
    }


    public function addTermEntry()
    {
        $entry = new Zend_Search_Lucene_Search_QueryEntry_Term($this->_currentToken->text, $this->_context->getField());
        $this->_context->addEntry($entry);
    }

    public function addPhraseEntry()
    {
        $entry = new Zend_Search_Lucene_Search_QueryEntry_Phrase($this->_currentToken->text, $this->_context->getField());
        $this->_context->addEntry($entry);
    }

    public function setField()
    {
        $this->_context->setNextEntryField($this->_currentToken->text);
    }

    public function setSign()
    {
        $this->_context->setNextEntrySign($this->_currentToken->type);
    }

    public function processFuzzyProximityModifier()
    {
        $this->_context->processFuzzyProximityModifier();
    }

    public function processModifierParameter()
    {
        if ($this->_lastToken === null) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Lexeme modifier parameter must follow lexeme modifier. Char position 0.' );
        }

        switch ($this->_lastToken->type) {
            case Zend_Search_Lucene_Search_QueryToken::TT_FUZZY_PROX_MARK:
                $this->_context->processFuzzyProximityModifier($this->_currentToken->text);
                break;

            case Zend_Search_Lucene_Search_QueryToken::TT_BOOSTING_MARK:
                $this->_context->boost($this->_currentToken->text);
                break;

            default:
                // It's not a user input exception
                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Lexeme modifier parameter must follow lexeme modifier. Char position 0.' );
        }
    }

    public function subqueryStart()
    {
        $this->_contextStack[] = $this->_context;
        $this->_context        = new Zend_Search_Lucene_Search_QueryParserContext($this->_encoding, $this->_context->getField());
    }

    public function subqueryEnd()
    {
        if (count($this->_contextStack) == 0) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Syntax Error: mismatched parentheses, every opening must have closing. Char position ' . $this->_currentToken->position . '.' );
        }

        $query          = $this->_context->getQuery();
        $this->_context = array_pop($this->_contextStack);

        $this->_context->addEntry(new Zend_Search_Lucene_Search_QueryEntry_Subquery($query));
    }

    public function logicalOperator()
    {
        $this->_context->addLogicalOperator($this->_currentToken->type);
    }

    public function openedRQFirstTerm()
    {
        $this->_rqFirstTerm = $this->_currentToken->text;
    }

    public function openedRQLastTerm()
    {
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_rqFirstTerm, $this->_encoding);
        if (count($tokens) > 1) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Range query boundary terms must be non-multiple word terms');
        } else if (count($tokens) == 1) {
            $from = new Zend_Search_Lucene_Index_Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $from = null;
        }

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_currentToken->text, $this->_encoding);
        if (count($tokens) > 1) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Range query boundary terms must be non-multiple word terms');
        } else if (count($tokens) == 1) {
            $to = new Zend_Search_Lucene_Index_Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $to = null;
        }

        if ($from === null  &&  $to === null) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('At least one range query boundary term must be non-empty term');
        }

        $rangeQuery = new Zend_Search_Lucene_Search_Query_Range($from, $to, false);
        $entry      = new Zend_Search_Lucene_Search_QueryEntry_Subquery($rangeQuery);
        $this->_context->addEntry($entry);
    }

    public function closedRQFirstTerm()
    {
        $this->_rqFirstTerm = $this->_currentToken->text;
    }

    public function closedRQLastTerm()
    {
        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_rqFirstTerm, $this->_encoding);
        if (count($tokens) > 1) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Range query boundary terms must be non-multiple word terms');
        } else if (count($tokens) == 1) {
            $from = new Zend_Search_Lucene_Index_Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $from = null;
        }

        $tokens = Zend_Search_Lucene_Analysis_Analyzer::getDefault()->tokenize($this->_currentToken->text, $this->_encoding);
        if (count($tokens) > 1) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('Range query boundary terms must be non-multiple word terms');
        } else if (count($tokens) == 1) {
            $to = new Zend_Search_Lucene_Index_Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $to = null;
        }

        if ($from === null  &&  $to === null) {
            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
            throw new Zend_Search_Lucene_Search_QueryParserException('At least one range query boundary term must be non-empty term');
        }

        $rangeQuery = new Zend_Search_Lucene_Search_Query_Range($from, $to, true);
        $entry      = new Zend_Search_Lucene_Search_QueryEntry_Subquery($rangeQuery);
        $this->_context->addEntry($entry);
    }
}

