<?php


require_once 'Zend/Validate/Interface.php';

class Zend_Validate implements Zend_Validate_Interface
{

    protected $_validators = array();

    protected $_messages = array();

    protected $_errors = array();

    public function addValidator(Zend_Validate_Interface $validator, $breakChainOnFailure = false)
    {
        $this->_validators[] = array(
            'instance' => $validator,
            'breakChainOnFailure' => (boolean) $breakChainOnFailure
            );
        return $this;
    }

    public function isValid($value)
    {
        $this->_messages = array();
        $this->_errors   = array();
        $result = true;
        foreach ($this->_validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value)) {
                continue;
            }
            $result = false;
            $messages = $validator->getMessages();
            $this->_messages = array_merge($this->_messages, $messages);
            $this->_errors   = array_merge($this->_errors,   array_keys($messages));
            if ($element['breakChainOnFailure']) {
                break;
            }
        }
        return $result;
    }

    public function getMessages()
    {
        return $this->_messages;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public static function is($value, $classBaseName, array $args = array(), $namespaces = array())
    {
        $namespaces = array_merge(array('Zend_Validate'), (array) $namespaces);
        foreach ($namespaces as $namespace) {
            $className = $namespace . '_' . ucfirst($classBaseName);
            try {
                require_once 'Zend/Loader.php';
                @Zend_Loader::loadClass($className);
                if (class_exists($className, false)) {
                    $class = new ReflectionClass($className);
                    if ($class->implementsInterface('Zend_Validate_Interface')) {
                        if ($class->hasMethod('__construct')) {
                            $object = $class->newInstanceArgs($args);
                        } else {
                            $object = $class->newInstance();
                        }
                        return $object->isValid($value);
                    }
                }
            } catch (Zend_Validate_Exception $ze) {
                // if there is an exception while validating throw it
                throw $ze;
            } catch (Zend_Exception $ze) {
                // fallthrough and continue for missing validation classes
            }
        }
        require_once 'Zend/Validate/Exception.php';
        throw new Zend_Validate_Exception("Validate class not found from basename '$classBaseName'");
    }

}
