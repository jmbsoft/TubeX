<?php


require_once 'Zend/Gdata/App/Extension.php';

require_once 'Zend/Gdata/App/Extension/Draft.php';

class Zend_Gdata_App_Extension_Control extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'app';
    protected $_rootElement = 'control';
    protected $_draft = null;

    public function __construct($draft = null)
    {
        parent::__construct();
        $this->_draft = $draft;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_draft != null) {
            $element->appendChild($this->_draft->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('app') . ':' . 'draft':
            $draft = new Zend_Gdata_App_Extension_Draft();
            $draft->transferFromDOM($child);
            $this->_draft = $draft;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getDraft()
    {
        return $this->_draft;
    }

    public function setDraft($value)
    {
        $this->_draft = $value;
        return $this;
    }

}
