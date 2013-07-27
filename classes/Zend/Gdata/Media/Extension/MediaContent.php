<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Media_Extension_MediaContent extends Zend_Gdata_Extension
{
    protected $_rootElement = 'content';
    protected $_rootNamespace = 'media';

    protected $_url = null;

    protected $_fileSize = null;

    protected $_type = null;

    protected $_medium = null;

    protected $_isDefault = null;

    protected $_expression = null;

    protected $_bitrate = null;

    protected $_framerate = null;

    protected $_samplingrate = null;

    protected $_channels = null;

    protected $_duration = null;

    protected $_height = null;

    protected $_width = null;

    protected $_lang = null;

    public function __construct($url = null, $fileSize = null, $type = null,
            $medium = null, $isDefault = null, $expression = null,
            $bitrate = null, $framerate = null, $samplingrate = null,
            $channels = null, $duration = null, $height = null, $width = null,
            $lang = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_fileSize = $fileSize;
        $this->_type = $type;
        $this->_medium = $medium;
        $this->_isDefault = $isDefault;
        $this->_expression = $expression;
        $this->_bitrate = $bitrate;
        $this->_framerate = $framerate;
        $this->_samplingrate = $samplingrate;
        $this->_channels = $channels;
        $this->_duration = $duration;
        $this->_height = $height;
        $this->_width = $width;
        $this->_lang = $lang;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_url !== null) {
            $element->setAttribute('url', $this->_url);
        }
        if ($this->_fileSize !== null) {
            $element->setAttribute('fileSize', $this->_fileSize);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        if ($this->_medium !== null) {
            $element->setAttribute('medium', $this->_medium);
        }
        if ($this->_isDefault !== null) {
            $element->setAttribute('isDefault', $this->_isDefault);
        }
        if ($this->_expression !== null) {
            $element->setAttribute('expression', $this->_expression);
        }
        if ($this->_bitrate !== null) {
            $element->setAttribute('bitrate', $this->_bitrate);
        }
        if ($this->_framerate !== null) {
            $element->setAttribute('framerate', $this->_framerate);
        }
        if ($this->_samplingrate !== null) {
            $element->setAttribute('samplingrate', $this->_samplingrate);
        }
        if ($this->_channels !== null) {
            $element->setAttribute('channels', $this->_channels);
        }
        if ($this->_duration !== null) {
            $element->setAttribute('duration', $this->_duration);
        }
        if ($this->_height !== null) {
            $element->setAttribute('height', $this->_height);
        }
        if ($this->_width !== null) {
            $element->setAttribute('width', $this->_width);
        }
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'url':
                $this->_url = $attribute->nodeValue;
                break;
            case 'fileSize':
                $this->_fileSize = $attribute->nodeValue;
                break;
            case 'type':
                $this->_type = $attribute->nodeValue;
                break;
            case 'medium':
                $this->_medium = $attribute->nodeValue;
                break;
            case 'isDefault':
                $this->_isDefault = $attribute->nodeValue;
                break;
            case 'expression':
                $this->_expression = $attribute->nodeValue;
                break;
            case 'bitrate':
                $this->_bitrate = $attribute->nodeValue;
                break;
            case 'framerate':
                $this->_framerate = $attribute->nodeValue;
                break;
            case 'samplingrate':
                $this->_samplingrate = $attribute->nodeValue;
                break;
            case 'channels':
                $this->_channels = $attribute->nodeValue;
                break;
            case 'duration':
                $this->_duration = $attribute->nodeValue;
                break;
            case 'height':
                $this->_height = $attribute->nodeValue;
                break;
            case 'width':
                $this->_width = $attribute->nodeValue;
                break;
            case 'lang':
                $this->_lang = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function setUrl($value)
    {
        $this->_url = $value;
        return $this;
    }

    public function getFileSize()
    {
        return $this->_fileSize;
    }

    public function setFileSize($value)
    {
        $this->_fileSize = $value;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    public function getMedium()
    {
        return $this->_medium;
    }

    public function setMedium($value)
    {
        $this->_medium = $value;
        return $this;
    }

    public function getIsDefault()
    {
        return $this->_isDefault;
    }

    public function setIsDefault($value)
    {
        $this->_isDefault = $value;
        return $this;
    }

    public function getExpression()
    {
        return $this->_expression;
    }

    public function setExpression($value)
    {
        $this->_expression = $value;
        return $this;
    }

    public function getBitrate()
    {
        return $this->_bitrate;
    }

    public function setBitrate($value)
    {
        $this->_bitrate = $value;
        return $this;
    }

    public function getFramerate()
    {
        return $this->_framerate;
    }

    public function setFramerate($value)
    {
        $this->_framerate = $value;
        return $this;
    }

    public function getSamplingrate()
    {
        return $this->_samplingrate;
    }

    public function setSamplingrate($value)
    {
        $this->_samplingrate = $value;
        return $this;
    }

    public function getChannels()
    {
        return $this->_channels;
    }

    public function setChannels($value)
    {
        $this->_channels = $value;
        return $this;
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

    public function getHeight()
    {
        return $this->_height;
    }

    public function setHeight($value)
    {
        $this->_height = $value;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function setWidth($value)
    {
        $this->_width = $value;
        return $this;
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function setLang($value)
    {
        $this->_lang = $value;
        return $this;
    }

}
