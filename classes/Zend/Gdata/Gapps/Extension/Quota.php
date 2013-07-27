<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Gapps.php';

class Zend_Gdata_Gapps_Extension_Quota extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'apps';
    protected $_rootElement = 'quota';

    protected $_limit = null;

    public function __construct($limit = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct();
        $this->_limit = $limit;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_limit !== null) {
            $element->setAttribute('limit', $this->_limit);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'limit':
            $this->_limit = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function setLimit($value)
    {
        $this->_limit = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getLimit();
    }

}
