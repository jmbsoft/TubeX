<?php


require_once 'Zend/Gdata/App/Extension/Link.php';

require_once 'Zend/Gdata/YouTube/Extension/Token.php';

class Zend_Gdata_YouTube_Extension_Link extends Zend_Gdata_App_Extension_Link
{

    protected $_token = null;

    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null, $token = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($href, $rel, $type, $hrefLang, $title, $length);
        $this->_token = $token;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_token != null) {
            $element->appendChild($this->_token->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'token':
            $token = new Zend_Gdata_YouTube_Extension_Token();
            $token->transferFromDOM($child);
            $this->_token = $token;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function setToken($value)
    {
        $this->_token = $value;
        return $this;
    }

    public function getTokenValue()
    {
      return $this->getToken()->getText();
    }

}
