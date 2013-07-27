<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Geo.php';

require_once 'Zend/Gdata/Geo/Extension/GmlPoint.php';

class Zend_Gdata_Geo_Extension_GeoRssWhere extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'georss';
    protected $_rootElement = 'where';

    protected $_point = null;

    public function __construct($point = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Geo::$namespaces);
        parent::__construct();
        $this->setPoint($point);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_point !== null) {
            $element->appendChild($this->_point->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gml') . ':' . 'Point';
                $point = new Zend_Gdata_Geo_Extension_GmlPoint();
                $point->transferFromDOM($child);
                $this->_point = $point;
                break;
        }
    }

    public function getPoint()
    {
        return $this->_point;
    }

    public function setPoint($value)
    {
        $this->_point = $value;
        return $this;
    }

}
