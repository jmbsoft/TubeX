<?php


require_once 'Zend/Gdata/App/Extension/Control.php';

require_once 'Zend/Gdata/YouTube/Extension/State.php';

class Zend_Gdata_YouTube_Extension_Control extends Zend_Gdata_App_Extension_Control
{

    protected $_state = null;

    public function __construct($draft = null, $state = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($draft);
        $this->_state = $state;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_state != null) {
            $element->appendChild($this->_state->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'state':
            $state = new Zend_Gdata_YouTube_Extension_State();
            $state->transferFromDOM($child);
            $this->_state = $state;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getState()
    {
        return $this->_state;
    }

    public function setState($value)
    {
        $this->_state = $value;
        return $this;
    }

    public function getStateValue()
    {
      return $this->getState()->getText();
    }

}
