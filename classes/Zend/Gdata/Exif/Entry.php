<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Exif.php';

require_once 'Zend/Gdata/Exif/Extension/Tags.php';

class Zend_Gdata_Exif_Entry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Exif_Entry';

    protected $_tags = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Exif::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_tags != null) {
            $element->appendChild($this->_tags->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('exif') . ':' . 'tags':
            $tags = new Zend_Gdata_Exif_Extension_Tags();
            $tags->transferFromDOM($child);
            $this->_tags = $tags;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getTags()
    {
        return $this->_tags;
    }

    public function setTags($value)
    {
        $this->_tags = $value;
        return $this;
    }

}
