<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaText extends Zend_Gdata_Extension
{

    protected $_rootElement = 'text';
    protected $_rootNamespace = 'media';

    protected $_type = null;

    protected $_lang = null;

    protected $_start = null;

    protected $_end = null;

    public function __construct($text = null, $type = null, $lang = null,
            $start = null, $end = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_type = $type;
        $this->_lang = $lang;
        $this->_start = $start;
        $this->_end = $end;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
        }
        if ($this->_start !== null) {
            $element->setAttribute('start', $this->_start);
        }
        if ($this->_end !== null) {
            $element->setAttribute('end', $this->_end);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        case 'lang':
            $this->_lang = $attribute->nodeValue;
            break;
        case 'start':
            $this->_start = $attribute->nodeValue;
            break;
        case 'end':
            $this->_end = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
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

    public function getLang()
    {
        return $this->_lang;
    }

    public function setLang($value)
    {
        $this->_lang = $value;
        return $this;
    }

    public function getStart()
    {
        return $this->_start;
    }

    public function setStart($value)
    {
        $this->_start = $value;
        return $this;
    }

    public function getEnd()
    {
        return $this->_end;
    }

    public function setEnd($value)
    {
        $this->_end = $value;
        return $this;
    }
}
