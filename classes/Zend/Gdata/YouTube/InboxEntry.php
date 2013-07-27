<?php


require_once 'Zend/Gdata/Media/Entry.php';

require_once 'Zend/Gdata/Extension/Rating.php';

require_once 'Zend/Gdata/Extension/Comments.php';

require_once 'Zend/Gdata/YouTube/Extension/Statistics.php';

require_once 'Zend/Gdata/YouTube/Extension/Description.php';

class Zend_Gdata_YouTube_InboxEntry extends Zend_Gdata_Media_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_InboxEntry';

    protected $_comments = null;

    protected $_rating = null;

    protected $_statistics = null;

    protected $_description = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_description != null) {
            $element->appendChild(
                $this->_description->getDOM($element->ownerDocument));
        }
        if ($this->_rating != null) {
            $element->appendChild(
                $this->_rating->getDOM($element->ownerDocument));
        }
        if ($this->_statistics != null) {
            $element->appendChild(
                $this->_statistics->getDOM($element->ownerDocument));
        }
        if ($this->_comments != null) {
            $element->appendChild(
                $this->_comments->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gd') . ':' . 'comments':
                $comments = new Zend_Gdata_Extension_Comments();
                $comments->transferFromDOM($child);
                $this->_comments = $comments;
                break;
            case $this->lookupNamespace('gd') . ':' . 'rating':
                $rating = new Zend_Gdata_Extension_Rating();
                $rating->transferFromDOM($child);
                $this->_rating = $rating;
                break;
            case $this->lookupNamespace('yt') . ':' . 'description':
                $description = new Zend_Gdata_YouTube_Extension_Description();
                $description->transferFromDOM($child);
                $this->_description = $description;
                break;
            case $this->lookupNamespace('yt') . ':' . 'statistics':
                $statistics = new Zend_Gdata_YouTube_Extension_Statistics();
                $statistics->transferFromDOM($child);
                $this->_statistics = $statistics;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getDescription()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getDescription ' .
                ' method is only supported in version 1 of the YouTube ' .
                'API.');
        } else {
            return $this->_description;
        }
    }

    public function setDescription($description = null)
    {
        if ($this->getMajorProtocolVersion() == 2) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setDescription ' .
                ' method is only supported in version 1 of the YouTube ' .
                'API.');
        } else {
            $this->_description = $description;
            return $this;
        }
    }

    public function getRating()
    {
        return $this->_rating;
    }

    public function setRating($rating = null)
    {
        $this->_rating = $rating;
        return $this;
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function setComments($comments = null)
    {
        $this->_comments = $comments;
        return $this;
    }

    public function getStatistics()
    {
        return $this->_statistics;
    }

    public function setStatistics($statistics = null)
    {
        $this->_statistics = $statistics;
        return $this;
    }


}
