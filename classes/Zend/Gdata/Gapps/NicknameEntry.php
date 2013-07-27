<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Gapps/Extension/Login.php';

require_once 'Zend/Gdata/Gapps/Extension/Nickname.php';

class Zend_Gdata_Gapps_NicknameEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gapps_NicknameEntry';

    protected $_login = null;

    protected $_nickname = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_login !== null) {
            $element->appendChild($this->_login->getDOM($element->ownerDocument));
        }
        if ($this->_nickname !== null) {
            $element->appendChild($this->_nickname->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('apps') . ':' . 'login';
                $login = new Zend_Gdata_Gapps_Extension_Login();
                $login->transferFromDOM($child);
                $this->_login = $login;
                break;
            case $this->lookupNamespace('apps') . ':' . 'nickname';
                $nickname = new Zend_Gdata_Gapps_Extension_Nickname();
                $nickname->transferFromDOM($child);
                $this->_nickname = $nickname;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getLogin()
    {
        return $this->_login;
    }

    public function setLogin($value)
    {
        $this->_login = $value;
        return $this;
    }

    public function getNickname()
    {
        return $this->_nickname;
    }

    public function setNickname($value)
    {
        $this->_nickname = $value;
        return $this;
    }

}
