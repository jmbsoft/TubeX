<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/YouTube/Extension/VideoId.php';

require_once 'Zend/Gdata/YouTube/Extension/Username.php';

require_once 'Zend/Gdata/Extension/Rating.php';

class Zend_Gdata_YouTube_ActivityEntry extends Zend_Gdata_Entry
{
    const ACTIVITY_CATEGORY_SCHEME =
        'http://gdata.youtube.com/schemas/2007/userevents.cat';

    protected $_entryClassName = 'Zend_Gdata_YouTube_ActivityEntry';

    protected $_videoId = null;

    protected $_username = null;

    protected $_rating = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_videoId !== null) {
          $element->appendChild($this->_videoId->getDOM(
              $element->ownerDocument));
        }
        if ($this->_username !== null) {
          $element->appendChild($this->_username->getDOM(
              $element->ownerDocument));
        }
        if ($this->_rating !== null) {
          $element->appendChild($this->_rating->getDOM(
              $element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('yt') . ':' . 'videoid':
                $videoId = new Zend_Gdata_YouTube_Extension_VideoId();
                $videoId->transferFromDOM($child);
                $this->_videoId = $videoId;
                break;
            case $this->lookupNamespace('yt') . ':' . 'username':
                $username = new Zend_Gdata_YouTube_Extension_Username();
                $username->transferFromDOM($child);
                $this->_username = $username;
                break;
            case $this->lookupNamespace('gd') . ':' . 'rating':
                $rating = new Zend_Gdata_Extension_Rating();
                $rating->transferFromDOM($child);
                $this->_rating = $rating;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getVideoId()
    {
        return $this->_videoId;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function getRating()
    {
        return $this->_rating;
    }

    public function getRatingValue()
    {
        $rating = $this->_rating;
        if ($rating) {
            return $rating->getValue();
        }
        return null;
    }

    public function getActivityType()
    {
        $categories = $this->getCategory();
        foreach($categories as $category) {
            if ($category->getScheme() == self::ACTIVITY_CATEGORY_SCHEME) {
                return $category->getTerm();
            }
        }
        return null;
    }

    public function getAuthorName()
    {
        $authors = $this->getAuthor();
        return $authors[0]->getName()->getText();
    }
}
