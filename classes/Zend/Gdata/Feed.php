<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/App/Feed.php';

require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/OpenSearchTotalResults.php';

require_once 'Zend/Gdata/Extension/OpenSearchStartIndex.php';

require_once 'Zend/Gdata/Extension/OpenSearchItemsPerPage.php';

class Zend_Gdata_Feed extends Zend_Gdata_App_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Entry';

    protected $_totalResults = null;

    protected $_startIndex = null;

    protected $_itemsPerPage = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_totalResults != null) {
            $element->appendChild($this->_totalResults->getDOM($element->ownerDocument));
        }
        if ($this->_startIndex != null) {
            $element->appendChild($this->_startIndex->getDOM($element->ownerDocument));
        }
        if ($this->_itemsPerPage != null) {
            $element->appendChild($this->_itemsPerPage->getDOM($element->ownerDocument));
        }

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
        case $this->lookupNamespace('openSearch') . ':' . 'totalResults':
            $totalResults = new Zend_Gdata_Extension_OpenSearchTotalResults();
            $totalResults->transferFromDOM($child);
            $this->_totalResults = $totalResults;
            break;
        case $this->lookupNamespace('openSearch') . ':' . 'startIndex':
            $startIndex = new Zend_Gdata_Extension_OpenSearchStartIndex();
            $startIndex->transferFromDOM($child);
            $this->_startIndex = $startIndex;
            break;
        case $this->lookupNamespace('openSearch') . ':' . 'itemsPerPage':
            $itemsPerPage = new Zend_Gdata_Extension_OpenSearchItemsPerPage();
            $itemsPerPage->transferFromDOM($child);
            $this->_itemsPerPage = $itemsPerPage;
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

    function setTotalResults($value) {
        $this->_totalResults = $value;
        return $this;
    }

    function getTotalResults() {
        return $this->_totalResults;
    }

    function setStartIndex($value) {
        $this->_startIndex = $value;
        return $this;
    }

    function getStartIndex() {
        return $this->_startIndex;
    }

    function setItemsPerPage($value) {
        $this->_itemsPerPage = $value;
        return $this;
    }

    function getItemsPerPage() {
        return $this->_itemsPerPage;
    }

}
