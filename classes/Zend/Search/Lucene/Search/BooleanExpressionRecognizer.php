<?php


require_once 'Zend/Search/Lucene/FSM.php';

require_once 'Zend/Search/Lucene/Search/QueryToken.php';

require_once 'Zend/Search/Lucene/Search/QueryParser.php';

class Zend_Search_Lucene_Search_BooleanExpressionRecognizer extends Zend_Search_Lucene_FSM
{

    const ST_START           = 0;
    const ST_LITERAL         = 1;
    const ST_NOT_OPERATOR    = 2;
    const ST_AND_OPERATOR    = 3;
    const ST_OR_OPERATOR     = 4;

    const IN_LITERAL         = 0;
    const IN_NOT_OPERATOR    = 1;
    const IN_AND_OPERATOR    = 2;
    const IN_OR_OPERATOR     = 3;

    private $_negativeLiteral = false;

    private $_literal;

    private $_conjunctions = array();

    private $_currentConjunction = array();

    public function __construct()
    {
        parent::__construct( array(self::ST_START,
                                   self::ST_LITERAL,
                                   self::ST_NOT_OPERATOR,
                                   self::ST_AND_OPERATOR,
                                   self::ST_OR_OPERATOR),
                             array(self::IN_LITERAL,
                                   self::IN_NOT_OPERATOR,
                                   self::IN_AND_OPERATOR,
                                   self::IN_OR_OPERATOR));

        $emptyOperatorAction    = new Zend_Search_Lucene_FSMAction($this, 'emptyOperatorAction');
        $emptyNotOperatorAction = new Zend_Search_Lucene_FSMAction($this, 'emptyNotOperatorAction');

        $this->addRules(array( array(self::ST_START,        self::IN_LITERAL,        self::ST_LITERAL),
                               array(self::ST_START,        self::IN_NOT_OPERATOR,   self::ST_NOT_OPERATOR),

                               array(self::ST_LITERAL,      self::IN_AND_OPERATOR,   self::ST_AND_OPERATOR),
                               array(self::ST_LITERAL,      self::IN_OR_OPERATOR,    self::ST_OR_OPERATOR),
                               array(self::ST_LITERAL,      self::IN_LITERAL,        self::ST_LITERAL,      $emptyOperatorAction),
                               array(self::ST_LITERAL,      self::IN_NOT_OPERATOR,   self::ST_NOT_OPERATOR, $emptyNotOperatorAction),

                               array(self::ST_NOT_OPERATOR, self::IN_LITERAL,        self::ST_LITERAL),

                               array(self::ST_AND_OPERATOR, self::IN_LITERAL,        self::ST_LITERAL),
                               array(self::ST_AND_OPERATOR, self::IN_NOT_OPERATOR,   self::ST_NOT_OPERATOR),

                               array(self::ST_OR_OPERATOR,  self::IN_LITERAL,        self::ST_LITERAL),
                               array(self::ST_OR_OPERATOR,  self::IN_NOT_OPERATOR,   self::ST_NOT_OPERATOR),
                             ));

        $notOperatorAction     = new Zend_Search_Lucene_FSMAction($this, 'notOperatorAction');
        $orOperatorAction      = new Zend_Search_Lucene_FSMAction($this, 'orOperatorAction');
        $literalAction         = new Zend_Search_Lucene_FSMAction($this, 'literalAction');


        $this->addEntryAction(self::ST_NOT_OPERATOR, $notOperatorAction);
        $this->addEntryAction(self::ST_OR_OPERATOR,  $orOperatorAction);
        $this->addEntryAction(self::ST_LITERAL,      $literalAction);
    }

    public function processOperator($operator)
    {
        $this->process($operator);
    }

    public function processLiteral($literal)
    {
        $this->_literal = $literal;

        $this->process(self::IN_LITERAL);
    }

    public function finishExpression()
    {
        if ($this->getState() != self::ST_LITERAL) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Literal expected.');
        }

        $this->_conjunctions[] = $this->_currentConjunction;

        return $this->_conjunctions;
    }


    public function emptyOperatorAction()
    {
        if (Zend_Search_Lucene_Search_QueryParser::getDefaultOperator() == Zend_Search_Lucene_Search_QueryParser::B_AND) {
            // Do nothing
        } else {
            $this->orOperatorAction();
        }

        // Process literal
        $this->literalAction();
    }

    public function emptyNotOperatorAction()
    {
        if (Zend_Search_Lucene_Search_QueryParser::getDefaultOperator() == Zend_Search_Lucene_Search_QueryParser::B_AND) {
            // Do nothing
        } else {
            $this->orOperatorAction();
        }

        // Process NOT operator
        $this->notOperatorAction();
    }

    public function notOperatorAction()
    {
        $this->_negativeLiteral = true;
    }

    public function orOperatorAction()
    {
        $this->_conjunctions[]     = $this->_currentConjunction;
        $this->_currentConjunction = array();
    }

    public function literalAction()
    {
        // Add literal to the current conjunction
        $this->_currentConjunction[] = array($this->_literal, !$this->_negativeLiteral);

        // Switch off negative signal
        $this->_negativeLiteral = false;
    }
}
