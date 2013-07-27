<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Books_Extension_Review extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gbs';
    protected $_rootElement = 'review';
    protected $_lang = null;
    protected $_type = null;

    public function __construct($lang = null, $type = null, $value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct();
        $this->_lang = $lang;
        $this->_type = $type;
        $this->_text = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc);
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'lang':
            $this->_lang = $attribute->nodeValue;
            break;
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getLang()
    {
        return $this->_lang;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setLang($lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }


}

