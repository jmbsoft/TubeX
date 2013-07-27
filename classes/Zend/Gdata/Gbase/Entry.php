<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Gbase/Extension/BaseAttribute.php';

class Zend_Gdata_Gbase_Entry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gbase_Entry';

    protected $_baseAttributes = array();

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gbase::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_baseAttributes as $baseAttribute) {
            $element->appendChild($baseAttribute->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        if (strstr($absoluteNodeName, $this->lookupNamespace('g') . ':')) {
            $baseAttribute = new Zend_Gdata_Gbase_Extension_BaseAttribute();
            $baseAttribute->transferFromDOM($child);
            $this->_baseAttributes[] = $baseAttribute;
        } else {
            parent::takeChildFromDOM($child);
        }
    }

    public function getItemType()
    {
        $itemType = $this->getGbaseAttribute('item_type');
        if (is_object($itemType[0])) {
          return $itemType[0];
        } else {
          return null;
        }
    }

    public function getGbaseAttributes() {
        return $this->_baseAttributes;
    }

    public function getGbaseAttribute($name)
    {
        $matches = array();
        for ($i = 0; $i < count($this->_baseAttributes); $i++) {
            $baseAttribute = $this->_baseAttributes[$i];
            if ($baseAttribute->rootElement == $name &&
                $baseAttribute->rootNamespaceURI == $this->lookupNamespace('g')) {
                $matches[] = &$this->_baseAttributes[$i];
            }
        }
        return $matches;
    }

}
