<?php


require_once 'Zend/Search/Lucene/FSMAction.php';

abstract class Zend_Search_Lucene_FSM
{

    private $_states = array();

    private $_currentState = null;

    private $_inputAphabet = array();

    private $_rules = array();

    private $_entryActions =  array();

    private $_exitActions =  array();

    private $_inputActions =  array();

    private $_transitionActions =  array();

    public function __construct($states = array(), $inputAphabet = array(), $rules = array())
    {
        $this->addStates($states);
        $this->addInputSymbols($inputAphabet);
        $this->addRules($rules);
    }

    public function addStates($states)
    {
        foreach ($states as $state) {
            $this->addState($state);
        }
    }

    public function addState($state)
    {
        $this->_states[$state] = $state;

        if ($this->_currentState === null) {
            $this->_currentState = $state;
        }
    }

    public function setState($state)
    {
        if (!isset($this->_states[$state])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('State \'' . $state . '\' is not on of the possible FSM states.');
        }

        $this->_currentState = $state;
    }

    public function getState()
    {
        return $this->_currentState;
    }

    public function addInputSymbols($inputAphabet)
    {
        foreach ($inputAphabet as $inputSymbol) {
            $this->addInputSymbol($inputSymbol);
        }
    }

    public function addInputSymbol($inputSymbol)
    {
        $this->_inputAphabet[$inputSymbol] = $inputSymbol;
    }

    public function addRules($rules)
    {
        foreach ($rules as $rule) {
            $this->addrule($rule[0], $rule[1], $rule[2], isset($rule[3])?$rule[3]:null);
        }
    }

    public function addRule($sourceState, $input, $targetState, $inputAction = null)
    {
        if (!isset($this->_states[$sourceState])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $sourceState . ').');
        }
        if (!isset($this->_states[$targetState])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined target state (' . $targetState . ').');
        }
        if (!isset($this->_inputAphabet[$input])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined input symbol (' . $input . ').');
        }

        if (!isset($this->_rules[$sourceState])) {
            $this->_rules[$sourceState] = array();
        }
        if (isset($this->_rules[$sourceState][$input])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Rule for {state,input} pair (' . $sourceState . ', '. $input . ') is already defined.');
        }

        $this->_rules[$sourceState][$input] = $targetState;


        if ($inputAction !== null) {
            $this->addInputAction($sourceState, $input, $inputAction);
        }
    }

    public function addEntryAction($state, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }

        if (!isset($this->_entryActions[$state])) {
            $this->_entryActions[$state] = array();
        }

        $this->_entryActions[$state][] = $action;
    }

    public function addExitAction($state, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }

        if (!isset($this->_exitActions[$state])) {
            $this->_exitActions[$state] = array();
        }

        $this->_exitActions[$state][] = $action;
    }

    public function addInputAction($state, $inputSymbol, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$state])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined state (' . $state. ').');
        }
        if (!isset($this->_inputAphabet[$inputSymbol])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined input symbol (' . $inputSymbol. ').');
        }

        if (!isset($this->_inputActions[$state])) {
            $this->_inputActions[$state] = array();
        }
        if (!isset($this->_inputActions[$state][$inputSymbol])) {
            $this->_inputActions[$state][$inputSymbol] = array();
        }

        $this->_inputActions[$state][$inputSymbol][] = $action;
    }

    public function addTransitionAction($sourceState, $targetState, Zend_Search_Lucene_FSMAction $action)
    {
        if (!isset($this->_states[$sourceState])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $sourceState. ').');
        }
        if (!isset($this->_states[$targetState])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('Undefined source state (' . $targetState. ').');
        }

        if (!isset($this->_transitionActions[$sourceState])) {
            $this->_transitionActions[$sourceState] = array();
        }
        if (!isset($this->_transitionActions[$sourceState][$targetState])) {
            $this->_transitionActions[$sourceState][$targetState] = array();
        }

        $this->_transitionActions[$sourceState][$targetState][] = $action;
    }

    public function process($input)
    {
        if (!isset($this->_rules[$this->_currentState])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any rule for current state (' . $this->_currentState . ').');
        }
        if (!isset($this->_rules[$this->_currentState][$input])) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any rule for {current state, input} pair (' . $this->_currentState . ', ' . $input . ').');
        }

        $sourceState = $this->_currentState;
        $targetState = $this->_rules[$this->_currentState][$input];

        if ($sourceState != $targetState  &&  isset($this->_exitActions[$sourceState])) {
            foreach ($this->_exitActions[$sourceState] as $action) {
                $action->doAction();
            }
        }
        if (isset($this->_inputActions[$sourceState]) &&
            isset($this->_inputActions[$sourceState][$input])) {
            foreach ($this->_inputActions[$sourceState][$input] as $action) {
                $action->doAction();
            }
        }


        $this->_currentState = $targetState;

        if (isset($this->_transitionActions[$sourceState]) &&
            isset($this->_transitionActions[$sourceState][$targetState])) {
            foreach ($this->_transitionActions[$sourceState][$targetState] as $action) {
                $action->doAction();
            }
        }
        if ($sourceState != $targetState  &&  isset($this->_entryActions[$targetState])) {
            foreach ($this->_entryActions[$targetState] as $action) {
                $action->doAction();
            }
        }
    }

    public function reset()
    {
        if (count($this->_states) == 0) {
            require_once 'Zend/Search/Exception.php';
            throw new Zend_Search_Exception('There is no any state defined for FSM.');
        }

        $this->_currentState = $this->_states[0];
    }
}

