<?php


require_once 'Zend/Gdata/App/Extension.php';

require_once 'Zend/Gdata/App/Extension/Name.php';

require_once 'Zend/Gdata/App/Extension/Email.php';

require_once 'Zend/Gdata/App/Extension/Uri.php';

abstract class Zend_Gdata_App_Extension_Person extends Zend_Gdata_App_Extension
{

    protected $_rootElement = null;
    protected $_name = null;
    protected $_email = null;
    protected $_uri = null;

    public function __construct($name = null, $email = null, $uri = null)
    {
        parent::__construct();
        $this->_name = $name;
        $this->_email = $email;
        $this->_uri = $uri;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_name != null) {
            $element->appendChild($this->_name->getDOM($element->ownerDocument));
        }
        if ($this->_email != null) {
            $element->appendChild($this->_email->getDOM($element->ownerDocument));
        }
        if ($this->_uri != null) {
            $element->appendChild($this->_uri->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'name':
            $name = new Zend_Gdata_App_Extension_Name();
            $name->transferFromDOM($child);
            $this->_name = $name;
            break;
        case $this->lookupNamespace('atom') . ':' . 'email':
            $email = new Zend_Gdata_App_Extension_Email();
            $email->transferFromDOM($child);
            $this->_email = $email;
            break;
        case $this->lookupNamespace('atom') . ':' . 'uri':
            $uri = new Zend_Gdata_App_Extension_Uri();
            $uri->transferFromDOM($child);
            $this->_uri = $uri;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function setEmail($value)
    {
        $this->_email = $value;
        return $this;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function setUri($value)
    {
        $this->_uri = $value;
        return $this;
    }

}
