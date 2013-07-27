<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Health/Extension/Ccr.php';

class Zend_Gdata_Health_ProfileEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Health_ProfileEntry';

    protected $_ccrData = null;

    public function __construct($element = null)
    {
        foreach (Zend_Gdata_Health::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_ccrData !== null) {
          $element->appendChild($this->_ccrData->getDOM($element->ownerDocument));
        }
        
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        if (strstr($absoluteNodeName, $this->lookupNamespace('ccr') . ':')) {
            $ccrElement = new Zend_Gdata_Health_Extension_Ccr();
            $ccrElement->transferFromDOM($child);
            $this->_ccrData = $ccrElement;            
        } else {
            parent::takeChildFromDOM($child);
            
        }
    }

    public function setCcr($ccrXMLStr) {
        $ccrElement = null;
        if ($ccrXMLStr != null) {
          $ccrElement = new Zend_Gdata_Health_Extension_Ccr();
          $ccrElement->transferFromXML($ccrXMLStr);
          $this->_ccrData = $ccrElement;
        }
        return $ccrElement;
    }

    public function getCcr() {
        return $this->_ccrData;
    }
}
