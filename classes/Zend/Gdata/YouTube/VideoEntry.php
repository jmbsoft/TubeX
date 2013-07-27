<?php


require_once 'Zend/Gdata/Extension/Comments.php';

require_once 'Zend/Gdata/Extension/FeedLink.php';

require_once 'Zend/Gdata/YouTube/MediaEntry.php';

require_once 'Zend/Gdata/YouTube/Extension/MediaGroup.php';

require_once 'Zend/Gdata/YouTube/Extension/NoEmbed.php';

require_once 'Zend/Gdata/YouTube/Extension/Statistics.php';

require_once 'Zend/Gdata/YouTube/Extension/Link.php';

require_once 'Zend/Gdata/YouTube/Extension/Racy.php';

require_once 'Zend/Gdata/Extension/Rating.php';

require_once 'Zend/Gdata/Geo/Extension/GeoRssWhere.php';

require_once 'Zend/Gdata/YouTube/Extension/Control.php';

require_once 'Zend/Gdata/YouTube/Extension/Recorded.php';

require_once 'Zend/Gdata/YouTube/Extension/Location.php';

class Zend_Gdata_YouTube_VideoEntry extends Zend_Gdata_YouTube_MediaEntry
{

    const YOUTUBE_DEVELOPER_TAGS_SCHEMA = 'http://gdata.youtube.com/schemas/2007/developertags.cat';
    const YOUTUBE_CATEGORY_SCHEMA = 'http://gdata.youtube.com/schemas/2007/categories.cat';
    protected $_entryClassName = 'Zend_Gdata_YouTube_VideoEntry';

    protected $_noEmbed = null;

    protected $_statistics = null;

    protected $_racy = null;

    protected $_private = null;

    protected $_rating = null;

    protected $_comments = null;

    protected $_feedLink = array();

    protected $_where = null;

    protected $_recorded = null;

    protected $_location = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_noEmbed != null) {
            $element->appendChild($this->_noEmbed->getDOM(
                $element->ownerDocument));
        }
        if ($this->_statistics != null) {
            $element->appendChild($this->_statistics->getDOM(
                $element->ownerDocument));
        }
        if ($this->_racy != null) {
            $element->appendChild($this->_racy->getDOM(
                $element->ownerDocument));
        }
        if ($this->_recorded != null) {
            $element->appendChild($this->_recorded->getDOM(
                $element->ownerDocument));
        }
        if ($this->_location != null) {
            $element->appendChild($this->_location->getDOM(
                $element->ownerDocument));
        }
        if ($this->_rating != null) {
            $element->appendChild($this->_rating->getDOM(
                $element->ownerDocument));
        }
        if ($this->_comments != null) {
            $element->appendChild($this->_comments->getDOM(
                $element->ownerDocument));
        }
        if ($this->_feedLink != null) {
            foreach ($this->_feedLink as $feedLink) {
                $element->appendChild($feedLink->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_where != null) {
           $element->appendChild($this->_where->getDOM(
                $element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
        case $this->lookupNamespace('yt') . ':' . 'statistics':
            $statistics = new Zend_Gdata_YouTube_Extension_Statistics();
            $statistics->transferFromDOM($child);
            $this->_statistics = $statistics;
            break;
        case $this->lookupNamespace('yt') . ':' . 'racy':
            $racy = new Zend_Gdata_YouTube_Extension_Racy();
            $racy->transferFromDOM($child);
            $this->_racy = $racy;
            break;
        case $this->lookupNamespace('yt') . ':' . 'recorded':
            $recorded = new Zend_Gdata_YouTube_Extension_Recorded();
            $recorded->transferFromDOM($child);
            $this->_recorded = $recorded;
            break;
        case $this->lookupNamespace('yt') . ':' . 'location':
            $location = new Zend_Gdata_YouTube_Extension_Location();
            $location->transferFromDOM($child);
            $this->_location = $location;
            break;
        case $this->lookupNamespace('gd') . ':' . 'rating':
            $rating = new Zend_Gdata_Extension_Rating();
            $rating->transferFromDOM($child);
            $this->_rating = $rating;
            break;
        case $this->lookupNamespace('gd') . ':' . 'comments':
            $comments = new Zend_Gdata_Extension_Comments();
            $comments->transferFromDOM($child);
            $this->_comments = $comments;
            break;
        case $this->lookupNamespace('yt') . ':' . 'noembed':
            $noEmbed = new Zend_Gdata_YouTube_Extension_NoEmbed();
            $noEmbed->transferFromDOM($child);
            $this->_noEmbed = $noEmbed;
            break;
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        case $this->lookupNamespace('georss') . ':' . 'where':
            $where = new Zend_Gdata_Geo_Extension_GeoRssWhere();
            $where->transferFromDOM($child);
            $this->_where = $where;
            break;
        case $this->lookupNamespace('atom') . ':' . 'link';
            $link = new Zend_Gdata_YouTube_Extension_Link();
            $link->transferFromDOM($child);
            $this->_link[] = $link;
            break;
        case $this->lookupNamespace('app') . ':' . 'control':
            $control = new Zend_Gdata_YouTube_Extension_Control();
            $control->transferFromDOM($child);
            $this->_control = $control;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function setRecorded($recorded = null)
    {
        $this->_recorded = $recorded;
        return $this;
    }

    public function getRecorded()
    {
        return $this->_recorded;
    }

    public function setLocation($location = null)
    {
        $this->_location = $location;
        return $this;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function setNoEmbed($noEmbed = null)
    {
        $this->_noEmbed = $noEmbed;
        return $this;
    }

    public function getNoEmbed()
    {
        return $this->_noEmbed;
    }

    public function isVideoEmbeddable()
    {
        if ($this->getNoEmbed() == null) {
            return true;
        } else {
            return false;
        }
    }

    public function setStatistics($statistics = null)
    {
        $this->_statistics = $statistics;
        return $this;
    }

    public function getStatistics()
    {
        return $this->_statistics;
    }

    public function setRacy($racy = null)
    {
        if ($this->getMajorProtocolVersion() == 2) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException(
                'Calling getRacy() on a YouTube VideoEntry is deprecated ' .
                'as of version 2 of the API.');
        }

        $this->_racy = $racy;
        return $this;
    }

    public function getRacy()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException(
                'Calling getRacy() on a YouTube VideoEntry is deprecated ' .
                'as of version 2 of the API.');
        }
        return $this->_racy;
    }

    public function setRating($rating = null)
    {
        $this->_rating = $rating;
        return $this;
    }

    public function getRating()
    {
        return $this->_rating;
    }

    public function setComments($comments = null)
    {
        $this->_comments = $comments;
        return $this;
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function setFeedLink($feedLink = null)
    {
        $this->_feedLink = $feedLink;
        return $this;
    }

    public function getFeedLink($rel = null)
    {
        if ($rel == null) {
            return $this->_feedLink;
        } else {
            foreach ($this->_feedLink as $feedLink) {
                if ($feedLink->rel == $rel) {
                    return $feedLink;
                }
            }
            return null;
        }
    }

    public function getVideoResponsesLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_RESPONSES_REL);
    }

    public function getVideoRatingsLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_RATINGS_REL);
    }

    public function getVideoComplaintsLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_COMPLAINTS_REL);
    }

    public function getVideoId()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            $videoId = $this->getMediaGroup()->getVideoId()->text;
        } else {
            $fullId = $this->getId()->getText();
            $position = strrpos($fullId, '/');
            if ($position === false) {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                    'Slash not found in atom:id of ' . $fullId);
            } else {
                $videoId = substr($fullId, $position + 1);
            }
        }
        return $videoId;
    }

    public function getVideoRecorded()
    {
        $recorded = $this->getRecorded();
        if ($recorded != null) {
          return $recorded->getText();
        } else {
          return null;
        }
    }

    public function setVideoRecorded($recorded)
    {
        $this->setRecorded(
            new Zend_Gdata_YouTube_Extension_Recorded($recorded));
        return $this;
    }

    public function getWhere()
    {
        return $this->_where;
    }

    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }

    public function getVideoTitle()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getTitle() != null) {
            return $this->getMediaGroup()->getTitle()->getText();
        } else {
            return null;
        }
    }

    public function setVideoTitle($title)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setTitle(
            new Zend_Gdata_Media_Extension_MediaTitle($title));
        return $this;
    }

    public function setVideoDescription($description)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setDescription(
            new Zend_Gdata_Media_Extension_MediaDescription($description));
        return $this;
    }

    public function getVideoDescription()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getDescription() != null) {
            return $this->getMediaGroup()->getDescription()->getText();
        } else {
            return null;
        }
    }

    public function getVideoWatchPageUrl()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getPlayer() != null &&
             array_key_exists(0, $this->getMediaGroup()->getPlayer())) {
            $players = $this->getMediaGroup()->getPlayer();
            return $players[0]->getUrl();
        } else {
            return null;
        }
    }

    public function getVideoThumbnails()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getThumbnail() != null) {

            $thumbnailArray = array();

            foreach ($this->getMediaGroup()->getThumbnail() as $thumbnailObj) {
                $thumbnail = array();
                $thumbnail['time'] = $thumbnailObj->time;
                $thumbnail['height'] = $thumbnailObj->height;
                $thumbnail['width'] = $thumbnailObj->width;
                $thumbnail['url'] = $thumbnailObj->url;
                $thumbnailArray[] = $thumbnail;
            }
            return $thumbnailArray;
        } else {
            return array();
        }
    }

    public function getFlashPlayerUrl()
    {
        $this->ensureMediaGroupIsNotNull();
        foreach ($this->getMediaGroup()->getContent() as $content) {
                if ($content->getType() === 'application/x-shockwave-flash') {
                    return $content->getUrl();
                }
            }
        return null;
    }

    public function getVideoDuration()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getDuration() != null) {
            return $this->getMediaGroup()->getDuration()->getSeconds();
        } else {
            return null;
        }
    }

    public function isVideoPrivate()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getPrivate() != null) {
            return true;
        } else {
            return false;
        }
    }

    public function setVideoPrivate()
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setPrivate(new Zend_Gdata_YouTube_Extension_Private());
        return $this;
    }

    public function setVideoPublic()
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->private = null;
        return $this;
    }

    public function getVideoTags()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getKeywords() != null) {

            $keywords = $this->getMediaGroup()->getKeywords();
            $keywordsString = $keywords->getText();
            if (strlen(trim($keywordsString)) > 0) {
                return split('(, *)|,', $keywordsString);
            }
        }
        return array();
    }

    public function setVideoTags($tags)
    {
        $this->ensureMediaGroupIsNotNull();
        $keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
        if (is_array($tags)) {
            $tags = implode(', ', $tags);
        }
        $keywords->setText($tags);
        $this->getMediaGroup()->setKeywords($keywords);
        return $this;
    }

    public function getVideoViewCount()
    {
        if ($this->getStatistics() != null) {
            return $this->getStatistics()->getViewCount();
        } else {
            return null;
        }
    }

    public function getVideoGeoLocation()
    {
        if ($this->getWhere() != null &&
            $this->getWhere()->getPoint() != null &&
            ($position = $this->getWhere()->getPoint()->getPos()) != null) {

            $positionString = $position->__toString();

            if (strlen(trim($positionString)) > 0) {
                $positionArray = explode(' ', trim($positionString));
                if (count($positionArray) == 2) {
                    $returnArray = array();
                    $returnArray['latitude'] = $positionArray[0];
                    $returnArray['longitude'] = $positionArray[1];
                    return $returnArray;
                }
            }
        }
        return null;
    }

    public function getVideoRatingInfo()
    {
        if ($this->getRating() != null) {
            $returnArray = array();
            $returnArray['average'] = $this->getRating()->getAverage();
            $returnArray['numRaters'] = $this->getRating()->getNumRaters();
            return $returnArray;
        } else {
            return null;
        }
    }

    public function getVideoCategory()
    {
        $this->ensureMediaGroupIsNotNull();
        $categories = $this->getMediaGroup()->getCategory();
        if ($categories != null) {
            foreach($categories as $category) {
                if ($category->getScheme() == self::YOUTUBE_CATEGORY_SCHEMA) {
                    return $category->getText();
                }
            }
        }
        return null;
    }

    public function setVideoCategory($category)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setCategory(array(new Zend_Gdata_Media_Extension_MediaCategory($category, self::YOUTUBE_CATEGORY_SCHEMA)));
        return $this;
    }

    public function getVideoDeveloperTags()
    {
        $developerTags = null;
        $this->ensureMediaGroupIsNotNull();

        $categoryArray = $this->getMediaGroup()->getCategory();
        if ($categoryArray != null) {
            foreach ($categoryArray as $category) {
                if ($category instanceof Zend_Gdata_Media_Extension_MediaCategory) {
                    if ($category->getScheme() == self::YOUTUBE_DEVELOPER_TAGS_SCHEMA) {
                        $developerTags[] = $category->getText();
                    }
                }
            }
            return $developerTags;
        }
        return null;
    }

    public function addVideoDeveloperTag($developerTag)
    {
        $this->ensureMediaGroupIsNotNull();
        $newCategory = new Zend_Gdata_Media_Extension_MediaCategory($developerTag, self::YOUTUBE_DEVELOPER_TAGS_SCHEMA);

        if ($this->getMediaGroup()->getCategory() == null) {
            $this->getMediaGroup()->setCategory($newCategory);
        } else {
            $categories = $this->getMediaGroup()->getCategory();
            $categories[] = $newCategory;
            $this->getMediaGroup()->setCategory($categories);
        }
        return $this;
    }

    public function setVideoDeveloperTags($developerTags)
    {
        foreach($developerTags as $developerTag) {
            $this->addVideoDeveloperTag($developerTag);
        }
        return $this;
    }

    public function getVideoState()
    {
        $control = $this->getControl();
        if ($control != null &&
            $control->getDraft() != null &&
            $control->getDraft()->getText() == 'yes') {

            return $control->getState();
        }
        return null;
    }

    public function ensureMediaGroupIsNotNull()
    {
        if ($this->getMediagroup() == null) {
            $this->setMediagroup(new Zend_Gdata_YouTube_Extension_MediaGroup());
        }
    }

    public function setVideoRating($ratingValue)
    {
        if ($ratingValue < 1 || $ratingValue > 5) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Rating for video entry must be between 1 and 5 inclusive.');
        }

         require_once 'Zend/Gdata/Extension/Rating.php';
         $rating = new Zend_Gdata_Extension_Rating(null, 1, 5, null,
            $ratingValue);
        $this->setRating($rating);
        return $this;
    }

    public function getVideoCommentFeedUrl()
    {
        $commentsExtension = $this->getComments();
        $commentsFeedUrl = null;
        if ($commentsExtension) {
            $commentsFeedLink = $commentsExtension->getFeedLink();
            if ($commentsFeedLink) {
                $commentsFeedUrl = $commentsFeedLink->getHref();
            }
        }
        return $commentsFeedUrl;
    }

}
