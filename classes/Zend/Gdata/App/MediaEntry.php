<?php


require_once 'Zend/Gdata/App/Entry.php';

require_once 'Zend/Gdata/App/MediaSource.php';

require_once 'Zend/Gdata/MediaMimeStream.php';

class Zend_Gdata_App_MediaEntry extends Zend_Gdata_App_Entry
{

    protected $_mediaSource = null;

    public function __construct($element = null, $mediaSource = null)
    {
        parent::__construct($element);
        $this->_mediaSource = $mediaSource;
    }

    public function encode()
    {
        $xmlData = $this->saveXML();
        $mediaSource = $this->getMediaSource();
        if ($mediaSource === null) {
            // No attachment, just send XML for entry
            return $xmlData;
        } else {
            return new Zend_Gdata_MediaMimeStream($xmlData,
                $mediaSource->getFilename(), $mediaSource->getContentType());
        }
    }

    public function getMediaSource()
    {
        return $this->_mediaSource;
    }

    public function setMediaSource($value)
    {
        if ($value instanceof Zend_Gdata_App_MediaSource) {
            $this->_mediaSource = $value;
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You must specify the media data as a class that conforms to Zend_Gdata_App_MediaSource.');
        }
        return $this;
    }

}
