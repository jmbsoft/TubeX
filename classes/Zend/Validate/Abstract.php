<?php


require_once 'Zend/Validate/Interface.php';

abstract class Zend_Validate_Abstract implements Zend_Validate_Interface
{

    protected $_value;

    protected $_messageVariables = array();

    protected $_messageTemplates = array();

    protected $_messages = array();

    protected $_obscureValue = false;

    protected $_errors = array();

    protected $_translator;

    protected static $_defaultTranslator;

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getMessageVariables()
    {
        return array_keys($this->_messageVariables);
    }

    public function setMessage($messageString, $messageKey = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->_messageTemplates);
            $messageKey = current($keys);
        }
        if (!isset($this->_messageTemplates[$messageKey])) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("No message template exists for key '$messageKey'");
        }
        $this->_messageTemplates[$messageKey] = $messageString;
        return $this;
    }

    public function setMessages(array $messages)
    {
        foreach ($messages as $key => $message) {
            $this->setMessage($message, $key);
        }
        return $this;
    }

    public function __get($property)
    {
        if ($property == 'value') {
            return $this->_value;
        }
        if (array_key_exists($property, $this->_messageVariables)) {
            return $this->{$this->_messageVariables[$property]};
        }

        require_once 'Zend/Validate/Exception.php';
        throw new Zend_Validate_Exception("No property exists by the name '$property'");
    }

    protected function _createMessage($messageKey, $value)
    {
        if (!isset($this->_messageTemplates[$messageKey])) {
            return null;
        }

        $message = $this->_messageTemplates[$messageKey];

        if (null !== ($translator = $this->getTranslator())) {
            if ($translator->isTranslated($message)) {
                $message = $translator->translate($message);
            } elseif ($translator->isTranslated($messageKey)) {
                $message = $translator->translate($messageKey);
            }
        }

        if (is_object($value)) {
        	if (!in_array('__toString', get_class_methods($value))) {
        		$value = get_class($value) . ' object';
        	} else {
        		$value = $value->__toString();
        	}
        } else {
        	$value = (string)$value;
        }

        if ($this->getObscureValue()) {
            $value = str_repeat('*', strlen($value));
        }

        $message = str_replace('%value%', (string) $value, $message);
        foreach ($this->_messageVariables as $ident => $property) {
            $message = str_replace("%$ident%", (string) $this->$property, $message);
        }
        return $message;
    }

    protected function _error($messageKey = null, $value = null)
    {
        if ($messageKey === null) {
            $keys = array_keys($this->_messageTemplates);
            $messageKey = current($keys);
        }
        if ($value === null) {
            $value = $this->_value;
        }
        $this->_errors[]              = $messageKey;
        $this->_messages[$messageKey] = $this->_createMessage($messageKey, $value);
    }

    protected function _setValue($value)
    {
        $this->_value    = $value;
        $this->_messages = array();
        $this->_errors   = array();
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function setObscureValue($flag)
    {
        $this->_obscureValue = (bool) $flag;
        return $this;
    }

    public function getObscureValue()
    {
        return $this->_obscureValue;
    }

    public function setTranslator($translator = null)
    {
        if ((null === $translator) || ($translator instanceof Zend_Translate_Adapter)) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid translator specified');
        }
        return $this;
    }

    public function getTranslator()
    {
        if (null === $this->_translator) {
            return self::getDefaultTranslator();
        }

        return $this->_translator;
    }

    public static function setDefaultTranslator($translator = null)
    {
        if ((null === $translator) || ($translator instanceof Zend_Translate_Adapter)) {
            self::$_defaultTranslator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            self::$_defaultTranslator = $translator->getAdapter();
        } else {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid translator specified');
        }
    }

    public static function getDefaultTranslator()
    {
        if (null === self::$_defaultTranslator) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $translator = Zend_Registry::get('Zend_Translate');
                if ($translator instanceof Zend_Translate_Adapter) {
                    return $translator;
                } elseif ($translator instanceof Zend_Translate) {
                    return $translator->getAdapter();
                }
            }
        }
        return self::$_defaultTranslator;
    }
}
