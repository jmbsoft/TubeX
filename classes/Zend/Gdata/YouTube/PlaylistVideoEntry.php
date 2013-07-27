<?php


require_once 'Zend/Gdata/YouTube/VideoEntry.php';

require_once 'Zend/Gdata/YouTube/Extension/Position.php';

class Zend_Gdata_YouTube_PlaylistVideoEntry extends Zend_Gdata_YouTube_VideoEntry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_PlaylistVideoEntry';

    protected $_position = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_position !== null) {
            $element->appendChild($this->_position->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'position':
            $position = new Zend_Gdata_YouTube_Extension_Position();
            $position->transferFromDOM($child);
            $this->_position = $position;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function setPosition($position = null)
    {
        $this->_position = $position;
        return $this;
    }

    public function getPosition()
    {
        return $this->_position;
    }

}
