<?php


require_once 'Zend/Gdata/App/Extension/Element.php';

require_once 'Zend/Gdata/App/Extension/Author.php';

require_once 'Zend/Gdata/App/Extension/Category.php';

require_once 'Zend/Gdata/App/Extension/Contributor.php';

require_once 'Zend/Gdata/App/Extension/Id.php';

require_once 'Zend/Gdata/App/Extension/Link.php';

require_once 'Zend/Gdata/App/Extension/Rights.php';

require_once 'Zend/Gdata/App/Extension/Title.php';

require_once 'Zend/Gdata/App/Extension/Updated.php';

require_once 'Zend/Version.php';

abstract class Zend_Gdata_App_FeedEntryParent extends Zend_Gdata_App_Base
{

    protected $_service = null;

    protected $_etag = NULL;

    protected $_author = array();
    protected $_category = array();
    protected $_contributor = array();
    protected $_id = null;
    protected $_link = array();
    protected $_rights = null;
    protected $_title = null;
    protected $_updated = null;

    protected $_majorProtocolVersion = 1;

    protected $_minorProtocolVersion = null;

    public function __construct($element = null)
    {
        if (!($element instanceof DOMElement)) {
            if ($element) {
                $this->transferFromXML($element);
            }
        } else {
            $this->transferFromDOM($element);
        }  
    }

    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        if (!$this->_service) {
            $this->_service = new Zend_Gdata_App();
        }
        $this->_service->setHttpClient($httpClient);
        return $this;
    }

    public function getHttpClient()
    {
        if (!$this->_service) {
            $this->_service = new Zend_Gdata_App();
        }
        $client = $this->_service->getHttpClient();
        return $client;
    }

    public function setService($instance)
    {
        $this->_service = $instance;
        return $this;
    }

    public function getService()
    {
        return $this->_service;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_author as $author) {
            $element->appendChild($author->getDOM($element->ownerDocument));
        }
        foreach ($this->_category as $category) {
            $element->appendChild($category->getDOM($element->ownerDocument));
        }
        foreach ($this->_contributor as $contributor) {
            $element->appendChild($contributor->getDOM($element->ownerDocument));
        }
        if ($this->_id != null) {
            $element->appendChild($this->_id->getDOM($element->ownerDocument));
        }
        foreach ($this->_link as $link) {
            $element->appendChild($link->getDOM($element->ownerDocument));
        }
        if ($this->_rights != null) {
            $element->appendChild($this->_rights->getDOM($element->ownerDocument));
        }
        if ($this->_title != null) {
            $element->appendChild($this->_title->getDOM($element->ownerDocument));
        }
        if ($this->_updated != null) {
            $element->appendChild($this->_updated->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('atom') . ':' . 'author':
            $author = new Zend_Gdata_App_Extension_Author();
            $author->transferFromDOM($child);
            $this->_author[] = $author;
            break;
        case $this->lookupNamespace('atom') . ':' . 'category':
            $category = new Zend_Gdata_App_Extension_Category();
            $category->transferFromDOM($child);
            $this->_category[] = $category;
            break;
        case $this->lookupNamespace('atom') . ':' . 'contributor':
            $contributor = new Zend_Gdata_App_Extension_Contributor();
            $contributor->transferFromDOM($child);
            $this->_contributor[] = $contributor;
            break;
        case $this->lookupNamespace('atom') . ':' . 'id':
            $id = new Zend_Gdata_App_Extension_Id();
            $id->transferFromDOM($child);
            $this->_id = $id;
            break;
        case $this->lookupNamespace('atom') . ':' . 'link':
            $link = new Zend_Gdata_App_Extension_Link();
            $link->transferFromDOM($child);
            $this->_link[] = $link;
            break;
        case $this->lookupNamespace('atom') . ':' . 'rights':
            $rights = new Zend_Gdata_App_Extension_Rights();
            $rights->transferFromDOM($child);
            $this->_rights = $rights;
            break;
        case $this->lookupNamespace('atom') . ':' . 'title':
            $title = new Zend_Gdata_App_Extension_Title();
            $title->transferFromDOM($child);
            $this->_title = $title;
            break;
        case $this->lookupNamespace('atom') . ':' . 'updated':
            $updated = new Zend_Gdata_App_Extension_Updated();
            $updated->transferFromDOM($child);
            $this->_updated = $updated;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getAuthor()
    {
        return $this->_author;
    }

    public function setAuthor($value)
    {
        $this->_author = $value;
        return $this;
    }

    public function getCategory()
    {
        return $this->_category;
    }

    public function setCategory($value)
    {
        $this->_category = $value;
        return $this;
    }

    public function getContributor()
    {
        return $this->_contributor;
    }

    public function setContributor($value)
    {
        $this->_contributor = $value;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    public function getLink($rel = null)
    {
        if ($rel == null) {
            return $this->_link;
        } else {
            foreach ($this->_link as $link) {
                if ($link->rel == $rel) {
                    return $link;
                }
            }
            return null;
        }
    }

    public function getEditLink()
    {
        return $this->getLink('edit');
    }

    public function getNextLink()
    {
        return $this->getLink('next');
    }

    public function getPreviousLink()
    {
        return $this->getLink('previous');
    }

    public function getLicenseLink()
    {
        return $this->getLink('license');
    }

    public function getSelfLink()
    {
        return $this->getLink('self');
    }

    public function getAlternateLink()
    {
        return $this->getLink('alternate');
    }

    public function setLink($value)
    {
        $this->_link = $value;
        return $this;
    }

    public function getRights()
    {
        return $this->_rights;
    }

    public function setRights($value)
    {
        $this->_rights = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getTitleValue()
    {
        if (($titleObj = $this->getTitle()) != null) {
            return $titleObj->getText();
        } else {
            return null;
        }
    }

    public function setTitle($value)
    {
        $this->_title = $value;
        return $this;
    }

    public function getUpdated()
    {
        return $this->_updated;
    }

    public function setUpdated($value)
    {
        $this->_updated = $value;
        return $this;
    }

    public function setEtag($value) {
        $this->_etag = $value;
        return $this;
    }

    public function getEtag() {
        return $this->_etag;
    }

    public function setMajorProtocolVersion($value)
    {
        if (!($value >= 1) && !is_null($value)) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Major protocol version must be >= 1');
        }
        $this->_majorProtocolVersion = $value;
    }

    public function getMajorProtocolVersion()
    {
        return $this->_majorProtocolVersion;
    }

    public function setMinorProtocolVersion($value)
    {
        if (!($value >= 0)) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Minor protocol version must be >= 0 or null');
        }
        $this->_minorProtocolVersion = $value;
    }

    public function getMinorProtocolVersion()
    {
        return $this->_minorProtocolVersion;
    }

    public function lookupNamespace($prefix,
                                    $majorVersion = null,
                                    $minorVersion = null)
    {
        // Auto-select current version
        if (is_null($majorVersion)) {
            $majorVersion = $this->getMajorProtocolVersion();
        }
        if (is_null($minorVersion)) {
            $minorVersion = $this->getMinorProtocolVersion();
        }
        
        // Perform lookup
        return parent::lookupNamespace($prefix, $majorVersion, $minorVersion);
    }

}
