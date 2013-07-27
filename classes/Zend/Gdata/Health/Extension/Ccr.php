<?php


require_once 'Zend/Gdata/App/Extension/Element.php';

class Zend_Gdata_Health_Extension_Ccr extends Zend_Gdata_App_Extension_Element
{
    protected $_rootNamespace = 'ccr';
    protected $_rootElement = 'ContinuityOfCareRecord';
    protected $_ccrDom = null;

    public function __construct($element = null)
    {
        foreach (Zend_Gdata_Health::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
    }

    public function transferFromDOM($node)
    {
        $this->_ccrDom = $node;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        if (is_null($doc)) {
            $doc = new DOMDocument('1.0', 'utf-8');
        }
        $domElement = $doc->importNode($this->_ccrDom, true);
        return $domElement;
    } 

    public function __call($name, $args)
    {
        $matches = array();

        if (substr($name, 0, 3) === 'get') {
            $category = substr($name, 3);

            switch ($category) {
                case 'Conditions':
                    $category = 'Problems';
                    break;
                case 'Allergies':
                    $category = 'Alerts';
                    break;
                case 'TestResults':
                    // TestResults is an alias for LabResults
                case 'LabResults':
                    $category = 'Results';
                    break;
                default:
                    // $category is already well formatted
            }

            return $this->_ccrDom->getElementsByTagNameNS($this->lookupNamespace('ccr'), $category);
        } else {
            return null;
        }
    }
}
