<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Media.php';

require_once 'Zend/Gdata/Media/Extension/MediaGroup.php';

class Zend_Gdata_Media_Entry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Media_Entry';

    protected $_mediaGroup = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_mediaGroup != null) {
            $element->appendChild($this->_mediaGroup->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('media') . ':' . 'group':
            $mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
            $mediaGroup->transferFromDOM($child);
            $this->_mediaGroup = $mediaGroup;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getMediaGroup()
    {
        return $this->_mediaGroup;
    }

    public function setMediaGroup($mediaGroup)
    {
        $this->_mediaGroup = $mediaGroup;
        return $this;
    }


}
