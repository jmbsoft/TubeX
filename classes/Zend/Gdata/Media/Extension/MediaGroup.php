<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Media/Extension/MediaContent.php';

require_once 'Zend/Gdata/Media/Extension/MediaCategory.php';

require_once 'Zend/Gdata/Media/Extension/MediaCopyright.php';

require_once 'Zend/Gdata/Media/Extension/MediaCredit.php';

require_once 'Zend/Gdata/Media/Extension/MediaDescription.php';

require_once 'Zend/Gdata/Media/Extension/MediaHash.php';

require_once 'Zend/Gdata/Media/Extension/MediaKeywords.php';

require_once 'Zend/Gdata/Media/Extension/MediaPlayer.php';

require_once 'Zend/Gdata/Media/Extension/MediaRating.php';

require_once 'Zend/Gdata/Media/Extension/MediaRestriction.php';

require_once 'Zend/Gdata/Media/Extension/MediaText.php';

require_once 'Zend/Gdata/Media/Extension/MediaThumbnail.php';

require_once 'Zend/Gdata/Media/Extension/MediaTitle.php';

class Zend_Gdata_Media_Extension_MediaGroup extends Zend_Gdata_Extension
{

    protected $_rootElement = 'group';
    protected $_rootNamespace = 'media';

    protected $_content = array();

    protected $_category = array();

    protected $_copyright = null;

    protected $_credit = array();

    protected $_description = null;

    protected $_hash = array();

    protected $_keywords = null;

    protected $_player = array();

    protected $_rating = array();

    protected $_restriction = array();

    protected $_mediaText = array();

    protected $_thumbnail = array();

    protected $_title = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        foreach ($this->_content as $content) {
            $element->appendChild($content->getDOM($element->ownerDocument));
        }
        foreach ($this->_category as $category) {
            $element->appendChild($category->getDOM($element->ownerDocument));
        }
        foreach ($this->_credit as $credit) {
            $element->appendChild($credit->getDOM($element->ownerDocument));
        }
        foreach ($this->_player as $player) {
            $element->appendChild($player->getDOM($element->ownerDocument));
        }
        foreach ($this->_rating as $rating) {
            $element->appendChild($rating->getDOM($element->ownerDocument));
        }
        foreach ($this->_restriction as $restriction) {
            $element->appendChild($restriction->getDOM($element->ownerDocument));
        }
        foreach ($this->_mediaText as $text) {
            $element->appendChild($text->getDOM($element->ownerDocument));
        }
        foreach ($this->_thumbnail as $thumbnail) {
            $element->appendChild($thumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_copyright != null) {
            $element->appendChild(
                    $this->_copyright->getDOM($element->ownerDocument));
        }
        if ($this->_description != null) {
            $element->appendChild(
                    $this->_description->getDOM($element->ownerDocument));
        }
        foreach ($this->_hash as $hash) {
            $element->appendChild($hash->getDOM($element->ownerDocument));
        }
        if ($this->_keywords != null) {
            $element->appendChild(
                    $this->_keywords->getDOM($element->ownerDocument));
        }
        if ($this->_title != null) {
            $element->appendChild(
                    $this->_title->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('media') . ':' . 'content';
                $content = new Zend_Gdata_Media_Extension_MediaContent();
                $content->transferFromDOM($child);
                $this->_content[] = $content;
                break;
            case $this->lookupNamespace('media') . ':' . 'category';
                $category = new Zend_Gdata_Media_Extension_MediaCategory();
                $category->transferFromDOM($child);
                $this->_category[] = $category;
                break;
            case $this->lookupNamespace('media') . ':' . 'copyright';
                $copyright = new Zend_Gdata_Media_Extension_MediaCopyright();
                $copyright->transferFromDOM($child);
                $this->_copyright = $copyright;
                break;
            case $this->lookupNamespace('media') . ':' . 'credit';
                $credit = new Zend_Gdata_Media_Extension_MediaCredit();
                $credit->transferFromDOM($child);
                $this->_credit[] = $credit;
                break;
            case $this->lookupNamespace('media') . ':' . 'description';
                $description = new Zend_Gdata_Media_Extension_MediaDescription();
                $description->transferFromDOM($child);
                $this->_description = $description;
                break;
            case $this->lookupNamespace('media') . ':' . 'hash';
                $hash = new Zend_Gdata_Media_Extension_MediaHash();
                $hash->transferFromDOM($child);
                $this->_hash[] = $hash;
                break;
            case $this->lookupNamespace('media') . ':' . 'keywords';
                $keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
                $keywords->transferFromDOM($child);
                $this->_keywords = $keywords;
                break;
            case $this->lookupNamespace('media') . ':' . 'player';
                $player = new Zend_Gdata_Media_Extension_MediaPlayer();
                $player->transferFromDOM($child);
                $this->_player[] = $player;
                break;
            case $this->lookupNamespace('media') . ':' . 'rating';
                $rating = new Zend_Gdata_Media_Extension_MediaRating();
                $rating->transferFromDOM($child);
                $this->_rating[] = $rating;
                break;
            case $this->lookupNamespace('media') . ':' . 'restriction';
                $restriction = new Zend_Gdata_Media_Extension_MediaRestriction();
                $restriction->transferFromDOM($child);
                $this->_restriction[] = $restriction;
                break;
            case $this->lookupNamespace('media') . ':' . 'text';
                $text = new Zend_Gdata_Media_Extension_MediaText();
                $text->transferFromDOM($child);
                $this->_mediaText[] = $text;
                break;
            case $this->lookupNamespace('media') . ':' . 'thumbnail';
                $thumbnail = new Zend_Gdata_Media_Extension_MediaThumbnail();
                $thumbnail->transferFromDOM($child);
                $this->_thumbnail[] = $thumbnail;
                break;
            case $this->lookupNamespace('media') . ':' . 'title';
                $title = new Zend_Gdata_Media_Extension_MediaTitle();
                $title->transferFromDOM($child);
                $this->_title = $title;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
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

    public function getCategory()
    {
        return $this->_category;
    }

    public function setCategory($value)
    {
        $this->_category = $value;
        return $this;
    }

    public function getCopyright()
    {
        return $this->_copyright;
    }

    public function setCopyright($value)
    {
        $this->_copyright = $value;
        return $this;
    }

    public function getCredit()
    {
        return $this->_credit;
    }

    public function setCredit($value)
    {
        $this->_credit = $value;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($value)
    {
        $this->_title = $value;
        return $this;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function setDescription($value)
    {
        $this->_description = $value;
        return $this;
    }

    public function getHash()
    {
        return $this->_hash;
    }

    public function setHash($value)
    {
        $this->_hash = $value;
        return $this;
    }

    public function getKeywords()
    {
        return $this->_keywords;
    }

    public function setKeywords($value)
    {
        $this->_keywords = $value;
        return $this;
    }

    public function getPlayer()
    {
        return $this->_player;
    }

    public function setPlayer($value)
    {
        $this->_player = $value;
        return $this;
    }

    public function getRating()
    {
        return $this->_rating;
    }

    public function setRating($value)
    {
        $this->_rating = $value;
        return $this;
    }

    public function getRestriction()
    {
        return $this->_restriction;
    }

    public function setRestriction($value)
    {
        $this->_restriction = $value;
        return $this;
    }

    public function getThumbnail()
    {
        return $this->_thumbnail;
    }

    public function setThumbnail($value)
    {
        $this->_thumbnail = $value;
        return $this;
    }

    public function getMediaText()
    {
        return $this->_mediaText;
    }

    public function setMediaText($value)
    {
        $this->_mediaText = $value;
        return $this;
    }

}
