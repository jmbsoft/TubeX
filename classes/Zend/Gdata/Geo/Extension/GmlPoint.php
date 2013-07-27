<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Geo.php';

require_once 'Zend/Gdata/Geo/Extension/GmlPos.php';

class Zend_Gdata_Geo_Extension_GmlPoint extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gml';
    protected $_rootElement = 'Point';

    protected $_pos = null;

    public function __construct($pos = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Geo::$namespaces);
        parent::__construct();
        $this->setPos($pos);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_pos !== null) {
            $element->appendChild($this->_pos->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gml') . ':' . 'pos';
                $pos = new Zend_Gdata_Geo_Extension_GmlPos();
                $pos->transferFromDOM($child);
                $this->_pos = $pos;
                break;
        }
    }

    public function getPos()
    {
        return $this->_pos;
    }

    public function setPos($value)
    {
        $this->_pos = $value;
        return $this;
    }


}
