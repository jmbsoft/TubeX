<?php


require_once 'Zend/Gdata/Media/Extension/MediaGroup.php';

require_once 'Zend/Gdata/YouTube/Extension/MediaContent.php';

require_once 'Zend/Gdata/YouTube/Extension/Duration.php';

require_once 'Zend/Gdata/YouTube/Extension/MediaRating.php';

require_once 'Zend/Gdata/YouTube/Extension/MediaCredit.php';

require_once 'Zend/Gdata/YouTube/Extension/Private.php';

require_once 'Zend/Gdata/YouTube/Extension/VideoId.php';

require_once 'Zend/Gdata/YouTube/Extension/Uploaded.php';

class Zend_Gdata_YouTube_Extension_MediaGroup extends Zend_Gdata_Media_Extension_MediaGroup
{

    protected $_rootElement = 'group';
    protected $_rootNamespace = 'media';

    protected $_duration = null;

    protected $_private = null;

    protected $_videoid = null;

    protected $_mediarating = null;

    protected $_mediacredit = null;

    protected $_uploaded = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_duration !== null) {
            $element->appendChild(
                $this->_duration->getDOM($element->ownerDocument));
        }
        if ($this->_private !== null) {
            $element->appendChild(
                $this->_private->getDOM($element->ownerDocument));
        }
        if ($this->_videoid != null) {
            $element->appendChild(
                $this->_videoid->getDOM($element->ownerDocument));
        }
        if ($this->_uploaded != null) {
            $element->appendChild(
                $this->_uploaded->getDOM($element->ownerDocument));
        }
        if ($this->_mediacredit != null) {
            $element->appendChild(
                $this->_mediacredit->getDOM($element->ownerDocument));
        }
        if ($this->_mediarating != null) {
            $element->appendChild(
                $this->_mediarating->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('media') . ':' . 'content':
                $content = new Zend_Gdata_YouTube_Extension_MediaContent();
                $content->transferFromDOM($child);
                $this->_content[] = $content;
                break;
            case $this->lookupNamespace('media') . ':' . 'rating':
                $mediarating = new Zend_Gdata_YouTube_Extension_MediaRating();
                $mediarating->transferFromDOM($child);
                $this->_mediarating = $mediarating;
                break;
            case $this->lookupNamespace('media') . ':' . 'credit':
                $mediacredit = new Zend_Gdata_YouTube_Extension_MediaCredit();
                $mediacredit->transferFromDOM($child);
                $this->_mediacredit = $mediacredit;
                break;
            case $this->lookupNamespace('yt') . ':' . 'duration':
                $duration = new Zend_Gdata_YouTube_Extension_Duration();
                $duration->transferFromDOM($child);
                $this->_duration = $duration;
                break;
            case $this->lookupNamespace('yt') . ':' . 'private':
                $private = new Zend_Gdata_YouTube_Extension_Private();
                $private->transferFromDOM($child);
                $this->_private = $private;
                break;
            case $this->lookupNamespace('yt') . ':' . 'videoid':
                $videoid = new Zend_Gdata_YouTube_Extension_VideoId();
                $videoid ->transferFromDOM($child);
                $this->_videoid = $videoid;
                break;
            case $this->lookupNamespace('yt') . ':' . 'uploaded':
                $uploaded = new Zend_Gdata_YouTube_Extension_Uploaded();
                $uploaded ->transferFromDOM($child);
                $this->_uploaded = $uploaded;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getDuration()
    {
        return $this->_duration;
    }

    public function setDuration($value)
    {
        $this->_duration = $value;
        return $this;
    }

    public function getVideoId()
    {
        return $this->_videoid;
    }

    public function setVideoId($value)
    {
        $this->_videoid = $value;
        return $this;
    }

    public function getUploaded()
    {
        return $this->_uploaded;
    }

    public function setUploaded($value)
    {
        $this->_uploaded = $value;
        return $this;
    }

    public function getPrivate()
    {
        return $this->_private;
    }

    public function setPrivate($value)
    {
        $this->_private = $value;
        return $this;
    }

    public function getMediaRating()
    {
        return $this->_mediarating;
    }

    public function setMediaRating($value)
    {
        $this->_mediarating = $value;
        return $this;
    }

    public function getMediaCredit()
    {
        return $this->_mediacredit;
    }

    public function setMediaCredit($value)
    {
        $this->_mediacredit = $value;
        return $this;
    }
}
