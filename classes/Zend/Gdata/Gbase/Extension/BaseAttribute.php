<?php


require_once 'Zend/Gdata/App/Extension/Element.php';

class Zend_Gdata_Gbase_Extension_BaseAttribute extends Zend_Gdata_App_Extension_Element
{

    protected $_rootNamespace = 'g';

    public function __construct($name = null, $text = null, $type = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gbase::$namespaces);
        if ($type !== null) {
          $attr = array('name' => 'type', 'value' => $type);
          $typeAttr = array('type' => $attr);
          $this->setExtensionAttributes($typeAttr);
        }
        parent::__construct($name,
                            $this->_rootNamespace,
                            $this->lookupNamespace($this->_rootNamespace),
                            $text);
    }

    public function getName() {
      return $this->_rootElement;
    }

    public function getType() {
      $typeAttr = $this->getExtensionAttributes();
      return $typeAttr['type']['value'];
    }

    public function setName($name) {
      $this->_rootElement = $name;
      return $this;
    }

    public function setType($type) {
      $attr = array('name' => 'type', 'value' => $type);
      $typeAttr = array('type' => $attr);
      $this->setExtensionAttributes($typeAttr);
      return $this;
    }

}
