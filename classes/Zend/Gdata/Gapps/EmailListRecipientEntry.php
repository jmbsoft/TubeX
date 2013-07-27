<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/Who.php';

class Zend_Gdata_Gapps_EmailListRecipientEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gapps_EmailListRecipientEntry';

    protected $_who = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_who !== null) {
            $element->appendChild($this->_who->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'who';
                $who = new Zend_Gdata_Extension_Who();
                $who->transferFromDOM($child);
                $this->_who = $who;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getWho()
    {
        return $this->_who;
    }

    public function setWho($value)
    {
        $this->_who = $value;
        return $this;
    }

}
