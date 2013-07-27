<?php


require_once 'Zend/Gdata/Media.php';

require_once 'Zend/Gdata/Media/Entry.php';

require_once 'Zend/Gdata/YouTube/Extension/MediaGroup.php';

class Zend_Gdata_YouTube_MediaEntry extends Zend_Gdata_Media_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_MediaEntry';

    protected $_mediaGroup = null;

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('media') . ':' . 'group':
            $mediaGroup = new Zend_Gdata_YouTube_Extension_MediaGroup();
            $mediaGroup->transferFromDOM($child);
            $this->_mediaGroup = $mediaGroup;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

}
