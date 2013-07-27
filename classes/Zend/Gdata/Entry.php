<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/App/MediaEntry.php';

class Zend_Gdata_Entry extends Zend_Gdata_App_MediaEntry
{

    protected $_entryClassName = 'Zend_Gdata_Entry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        // ETags are special. We only support them in protocol >= 2.X.
        // This will be duplicated by the HTTP ETag header.
        if ($majorVersion >= 2) {
            if ($this->_etag != null) {
                $element->setAttributeNS($this->lookupNamespace('gd'),
                                         'gd:etag',
                                         $this->_etag);
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'content':
            $content = new Zend_Gdata_App_Extension_Content();
            $content->transferFromDOM($child);
            $this->_content = $content;
            break;
        case $this->lookupNamespace('atom') . ':' . 'published':
            $published = new Zend_Gdata_App_Extension_Published();
            $published->transferFromDOM($child);
            $this->_published = $published;
            break;
        case $this->lookupNamespace('atom') . ':' . 'source':
            $source = new Zend_Gdata_App_Extension_Source();
            $source->transferFromDOM($child);
            $this->_source = $source;
            break;
        case $this->lookupNamespace('atom') . ':' . 'summary':
            $summary = new Zend_Gdata_App_Extension_Summary();
            $summary->transferFromDOM($child);
            $this->_summary = $summary;
            break;
        case $this->lookupNamespace('app') . ':' . 'control':
            $control = new Zend_Gdata_App_Extension_Control();
            $control->transferFromDOM($child);
            $this->_control = $control;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'etag':
            // ETags are special, since they can be conveyed by either the
            // HTTP ETag header or as an XML attribute.
            $etag = $attribute->nodeValue;
            if (is_null($this->_etag)) {
                $this->_etag = $etag;
            }
            elseif ($this->_etag != $etag) {
                require_once('Zend/Gdata/App/IOException.php');
                throw new Zend_Gdata_App_IOException("ETag mismatch");
            }
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
            break;
        }
    }

}
