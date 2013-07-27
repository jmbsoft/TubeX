<?php


require_once 'Zend/Gdata/App/FeedEntryParent.php';

require_once 'Zend/Gdata/App/Extension/Content.php';

require_once 'Zend/Gdata/App/Extension/Edited.php';

require_once 'Zend/Gdata/App/Extension/Published.php';

require_once 'Zend/Gdata/App/Extension/Source.php';

require_once 'Zend/Gdata/App/Extension/Summary.php';

require_once 'Zend/Gdata/App/Extension/Control.php';

class Zend_Gdata_App_Entry extends Zend_Gdata_App_FeedEntryParent
{

    protected $_rootElement = 'entry';

    protected $_entryClassName = 'Zend_Gdata_App_Entry';

    protected $_content = null;

    protected $_published = null;

    protected $_source = null;

    protected $_summary = null;

    protected $_control = null;

    protected $_edited = null;

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_content != null) {
            $element->appendChild($this->_content->getDOM($element->ownerDocument));
        }
        if ($this->_published != null) {
            $element->appendChild($this->_published->getDOM($element->ownerDocument));
        }
        if ($this->_source != null) {
            $element->appendChild($this->_source->getDOM($element->ownerDocument));
        }
        if ($this->_summary != null) {
            $element->appendChild($this->_summary->getDOM($element->ownerDocument));
        }
        if ($this->_control != null) {
            $element->appendChild($this->_control->getDOM($element->ownerDocument));
        }
        if ($this->_edited != null) {
            $element->appendChild($this->_edited->getDOM($element->ownerDocument));
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
        case $this->lookupNamespace('app') . ':' . 'edited':
            $edited = new Zend_Gdata_App_Extension_Edited();
            $edited->transferFromDOM($child);
            $this->_edited = $edited;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function save($uri = null, $className = null, $extraHeaders = array())
    {
        return $this->getService()->updateEntry($this,
                                                $uri,
                                                $className,
                                                $extraHeaders);
    }

    public function delete()
    {
        $this->getService()->delete($this);
    }

    public function reload($uri = null, $className = null, $extraHeaders = array())
    {
        // Get URI
        $editLink = $this->getEditLink();
        if (is_null($uri) && $editLink != null) {
            $uri = $editLink->getHref();
        }
        
        // Set classname to current class, if not otherwise set
        if (is_null($className)) {
            $className = get_class($this);
        }
        
        // Append ETag, if present (Gdata v2 and above, only) and doesn't
        // conflict with existing headers
        if ($this->_etag != null
                && !array_key_exists('If-Match', $extraHeaders)
                && !array_key_exists('If-None-Match', $extraHeaders)) {
            $extraHeaders['If-None-Match'] = $this->_etag;
        }
        
        // If an HTTP 304 status (Not Modified)is returned, then we return
        // null.
        $result = null;
        try {
            $result = $this->service->importUrl($uri, $className, $extraHeaders);
        } catch (Zend_Gdata_App_HttpException $e) {
            if ($e->getResponse()->getStatus() != '304')
                throw $e;
        }
        
        return $result;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent($value)
    {
        $this->_content = $value;
        return $this;
    }

    public function getPublished()
    {
        return $this->_published;
    }

    public function setPublished($value)
    {
        $this->_published = $value;
        return $this;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function setSource($value)
    {
        $this->_source = $value;
        return $this;
    }

    public function getSummary()
    {
        return $this->_summary;
    }

    public function setSummary($value)
    {
        $this->_summary = $value;
        return $this;
    }

    public function getControl()
    {
        return $this->_control;
    }

    public function setControl($value)
    {
        $this->_control = $value;
        return $this;
    }

}
