<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Geo.php';

require_once 'Zend/Gdata/Geo/Extension/GeoRssWhere.php';

class Zend_Gdata_Geo_Entry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Geo_Entry';

    protected $_where = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Geo::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_where != null) {
            $element->appendChild($this->_where->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('georss') . ':' . 'where':
            $where = new Zend_Gdata_Geo_Extension_GeoRssWhere();
            $where->transferFromDOM($child);
            $this->_where = $where;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getWhere()
    {
        return $this->_where;
    }

    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }


}
