<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension/Comments.php';

require_once 'Zend/Gdata/DublinCore/Extension/Creator.php';

require_once 'Zend/Gdata/DublinCore/Extension/Date.php';

require_once 'Zend/Gdata/DublinCore/Extension/Description.php';

require_once 'Zend/Gdata/Books/Extension/Embeddability.php';

require_once 'Zend/Gdata/DublinCore/Extension/Format.php';

require_once 'Zend/Gdata/DublinCore/Extension/Identifier.php';

require_once 'Zend/Gdata/DublinCore/Extension/Language.php';

require_once 'Zend/Gdata/DublinCore/Extension/Publisher.php';

require_once 'Zend/Gdata/Extension/Rating.php';

require_once 'Zend/Gdata/Books/Extension/Review.php';

require_once 'Zend/Gdata/DublinCore/Extension/Subject.php';

require_once 'Zend/Gdata/DublinCore/Extension/Title.php';

require_once 'Zend/Gdata/Books/Extension/Viewability.php';

class Zend_Gdata_Books_VolumeEntry extends Zend_Gdata_Entry
{

    const THUMBNAIL_LINK_REL = 'http://schemas.google.com/books/2008/thumbnail';
    const PREVIEW_LINK_REL = 'http://schemas.google.com/books/2008/preview';
    const INFO_LINK_REL = 'http://schemas.google.com/books/2008/info';
    const ANNOTATION_LINK_REL = 'http://schemas.google.com/books/2008/annotation';

    protected $_comments = null;
    protected $_creators = array();
    protected $_dates = array();
    protected $_descriptions = array();
    protected $_embeddability = null;
    protected $_formats = array();
    protected $_identifiers = array();
    protected $_languages = array();
    protected $_publishers = array();
    protected $_rating = null;
    protected $_review = null;
    protected $_subjects = array();
    protected $_titles = array();
    protected $_viewability = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc);
        if ($this->_creators !== null) {
            foreach ($this->_creators as $creators) {
                $element->appendChild($creators->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_dates !== null) {
            foreach ($this->_dates as $dates) {
                $element->appendChild($dates->getDOM($element->ownerDocument));
            }
        }
        if ($this->_descriptions !== null) {
            foreach ($this->_descriptions as $descriptions) {
                $element->appendChild($descriptions->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_formats !== null) {
            foreach ($this->_formats as $formats) {
                $element->appendChild($formats->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_identifiers !== null) {
            foreach ($this->_identifiers as $identifiers) {
                $element->appendChild($identifiers->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_languages !== null) {
            foreach ($this->_languages as $languages) {
                $element->appendChild($languages->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_publishers !== null) {
            foreach ($this->_publishers as $publishers) {
                $element->appendChild($publishers->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_subjects !== null) {
            foreach ($this->_subjects as $subjects) {
                $element->appendChild($subjects->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_titles !== null) {
            foreach ($this->_titles as $titles) {
                $element->appendChild($titles->getDOM($element->ownerDocument));
            }
        }
        if ($this->_comments !== null) {
            $element->appendChild($this->_comments->getDOM(
                $element->ownerDocument));
        }
        if ($this->_embeddability !== null) {
            $element->appendChild($this->_embeddability->getDOM(
                $element->ownerDocument));
        }
        if ($this->_rating !== null) {
            $element->appendChild($this->_rating->getDOM(
                $element->ownerDocument));
        }
        if ($this->_review !== null) {
            $element->appendChild($this->_review->getDOM(
                $element->ownerDocument));
        }
        if ($this->_viewability !== null) {
            $element->appendChild($this->_viewability->getDOM(
                $element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('dc') . ':' . 'creator':
            $creators = new Zend_Gdata_DublinCore_Extension_Creator();
            $creators->transferFromDOM($child);
            $this->_creators[] = $creators;
            break;
        case $this->lookupNamespace('dc') . ':' . 'date':
            $dates = new Zend_Gdata_DublinCore_Extension_Date();
            $dates->transferFromDOM($child);
            $this->_dates[] = $dates;
            break;
        case $this->lookupNamespace('dc') . ':' . 'description':
            $descriptions = new Zend_Gdata_DublinCore_Extension_Description();
            $descriptions->transferFromDOM($child);
            $this->_descriptions[] = $descriptions;
            break;
        case $this->lookupNamespace('dc') . ':' . 'format':
            $formats = new Zend_Gdata_DublinCore_Extension_Format();
            $formats->transferFromDOM($child);
            $this->_formats[] = $formats;
            break;
        case $this->lookupNamespace('dc') . ':' . 'identifier':
            $identifiers = new Zend_Gdata_DublinCore_Extension_Identifier();
            $identifiers->transferFromDOM($child);
            $this->_identifiers[] = $identifiers;
            break;
        case $this->lookupNamespace('dc') . ':' . 'language':
            $languages = new Zend_Gdata_DublinCore_Extension_Language();
            $languages->transferFromDOM($child);
            $this->_languages[] = $languages;
            break;
        case $this->lookupNamespace('dc') . ':' . 'publisher':
            $publishers = new Zend_Gdata_DublinCore_Extension_Publisher();
            $publishers->transferFromDOM($child);
            $this->_publishers[] = $publishers;
            break;
        case $this->lookupNamespace('dc') . ':' . 'subject':
            $subjects = new Zend_Gdata_DublinCore_Extension_Subject();
            $subjects->transferFromDOM($child);
            $this->_subjects[] = $subjects;
            break;
        case $this->lookupNamespace('dc') . ':' . 'title':
            $titles = new Zend_Gdata_DublinCore_Extension_Title();
            $titles->transferFromDOM($child);
            $this->_titles[] = $titles;
            break;
        case $this->lookupNamespace('gd') . ':' . 'comments':
            $comments = new Zend_Gdata_Extension_Comments();
            $comments->transferFromDOM($child);
            $this->_comments = $comments;
            break;
        case $this->lookupNamespace('gbs') . ':' . 'embeddability':
            $embeddability = new Zend_Gdata_Books_Extension_Embeddability();
            $embeddability->transferFromDOM($child);
            $this->_embeddability = $embeddability;
            break;
        case $this->lookupNamespace('gd') . ':' . 'rating':
            $rating = new Zend_Gdata_Extension_Rating();
            $rating->transferFromDOM($child);
            $this->_rating = $rating;
            break;
        case $this->lookupNamespace('gbs') . ':' . 'review':
            $review = new Zend_Gdata_Books_Extension_Review();
            $review->transferFromDOM($child);
            $this->_review = $review;
            break;
        case $this->lookupNamespace('gbs') . ':' . 'viewability':
            $viewability = new Zend_Gdata_Books_Extension_Viewability();
            $viewability->transferFromDOM($child);
            $this->_viewability = $viewability;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function getCreators()
    {
        return $this->_creators;
    }

    public function getDates()
    {
        return $this->_dates;
    }

    public function getDescriptions()
    {
        return $this->_descriptions;
    }

    public function getEmbeddability()
    {
        return $this->_embeddability;
    }

    public function getFormats()
    {
        return $this->_formats;
    }

    public function getIdentifiers()
    {
        return $this->_identifiers;
    }

    public function getLanguages()
    {
        return $this->_languages;
    }

    public function getPublishers()
    {
        return $this->_publishers;
    }

    public function getRating()
    {
        return $this->_rating;
    }

    public function getReview()
    {
        return $this->_review;
    }

    public function getSubjects()
    {
        return $this->_subjects;
    }

    public function getTitles()
    {
        return $this->_titles;
    }

    public function getViewability()
    {
        return $this->_viewability;
    }

    public function setComments($comments)
    {
        $this->_comments = $comments;
        return $this;
    }

    public function setCreators($creators)
    {
        $this->_creators = $creators;
        return $this;
    }

    public function setDates($dates)
    {
        $this->_dates = $dates;
        return $this;
    }

    public function setDescriptions($descriptions)
    {
        $this->_descriptions = $descriptions;
        return $this;
    }

    public function setEmbeddability($embeddability)
    {
        $this->_embeddability = $embeddability;
        return $this;
    }

    public function setFormats($formats)
    {
        $this->_formats = $formats;
        return $this;
    }

    public function setIdentifiers($identifiers)
    {
        $this->_identifiers = $identifiers;
        return $this;
    }

    public function setLanguages($languages)
    {
        $this->_languages = $languages;
        return $this;
    }

    public function setPublishers($publishers)
    {
        $this->_publishers = $publishers;
        return $this;
    }

    public function setRating($rating)
    {
        $this->_rating = $rating;
        return $this;
    }

    public function setReview($review)
    {
        $this->_review = $review;
        return $this;
    }

    public function setSubjects($subjects)
    {
        $this->_subjects = $subjects;
        return $this;
    }

    public function setTitles($titles)
    {
        $this->_titles = $titles;
        return $this;
    }

    public function setViewability($viewability)
    {
        $this->_viewability = $viewability;
        return $this;
    }

    public function getVolumeId()
    {
        $fullId = $this->getId()->getText();
        $position = strrpos($fullId, '/');
        if ($position === false) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('Slash not found in atom:id');
        } else {
            return substr($fullId, strrpos($fullId,'/') + 1);
        }
    }

    public function getThumbnailLink()
    {
        return $this->getLink(self::THUMBNAIL_LINK_REL);
    }

    public function getPreviewLink()
    {
        return $this->getLink(self::PREVIEW_LINK_REL);
    }

    public function getInfoLink()
    {
        return $this->getLink(self::INFO_LINK_REL);
    }

    public function getAnnotationLink()
    {
        return $this->getLink(self::ANNOTATION_LINK_REL);
    }

}
