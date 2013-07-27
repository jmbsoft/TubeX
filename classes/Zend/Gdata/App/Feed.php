<?php


require_once 'Zend/Gdata/App/Entry.php';

require_once 'Zend/Gdata/App/FeedSourceParent.php';

class Zend_Gdata_App_Feed extends Zend_Gdata_App_FeedSourceParent
        implements Iterator, ArrayAccess
{

    protected $_rootElement = 'feed';

    protected $_entry = array();

    protected $_entryIndex = 0;

    public function __get($var)
    {
        switch ($var) {
            case 'entries':
                return $this;
            default:
                return parent::__get($var);
        }
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_entry as $entry) {
            $element->appendChild($entry->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'entry':
            $newEntry = new $this->_entryClassName($child);
            $newEntry->setHttpClient($this->getHttpClient());
            $newEntry->setMajorProtocolVersion($this->getMajorProtocolVersion());
            $newEntry->setMinorProtocolVersion($this->getMinorProtocolVersion());
            $this->_entry[] = $newEntry;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function count()
    {
        return count($this->_entry);
    }

    public function rewind()
    {
        $this->_entryIndex = 0;
    }

    public function current()
    {
        return $this->_entry[$this->_entryIndex];
    }

    public function key()
    {
        return $this->_entryIndex;
    }

    public function next()
    {
        ++$this->_entryIndex;
    }

    public function valid()
    {
        return 0 <= $this->_entryIndex && $this->_entryIndex < $this->count();
    }

    public function getEntry()
    {
        return $this->_entry;
    }

    public function setEntry($value)
    {
        $this->_entry = $value;
        return $this;
    }

    public function addEntry($value)
    {
        $this->_entry[] = $value;
        return $this;
    }

    public function offsetSet($key, $value)
    {
        $this->_entry[$key] = $value;
    }

    public function offsetGet($key)
    {
        if (array_key_exists($key, $this->_entry)) {
            return $this->_entry[$key];
        }
    }

    public function offsetUnset($key)
    {
        if (array_key_exists($key, $this->_entry)) {
            unset($this->_entry[$key]);
        }
    }

    public function offsetExists($key)
    {
        return (array_key_exists($key, $this->_entry));
    }

    public function getNextFeed()
    {
        $nextLink = $this->getNextLink();
        if (!$nextLink) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_Exception('No link to next set ' .
            'of results found.');
        }
        $nextLinkHref = $nextLink->getHref();
        $service = new Zend_Gdata_App($this->getHttpClient());

        return $service->getFeed($nextLinkHref, get_class($this));
    }

    public function getPreviousFeed()
    {
        $previousLink = $this->getPreviousLink();
        if (!$previousLink) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_Exception('No link to previous set ' .
            'of results found.');
        }
        $previousLinkHref = $previousLink->getHref();
        $service = new Zend_Gdata_App($this->getHttpClient());

        return $service->getFeed($previousLinkHref, get_class($this));
    }

    public function setMajorProtocolVersion($value)
    {
        parent::setMajorProtocolVersion($value);
        foreach ($this->entries as $entry) {
            $entry->setMajorProtocolVersion($value);
        }
    }

    public function setMinorProtocolVersion($value)
    {
        parent::setMinorProtocolVersion($value);
        foreach ($this->entries as $entry) {
            $entry->setMinorProtocolVersion($value);
        }
    }

}
