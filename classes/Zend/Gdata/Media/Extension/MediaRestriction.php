<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaRestriction extends Zend_Gdata_Extension
{

    protected $_rootElement = 'restriction';
    protected $_rootNamespace = 'media';

    protected $_relationship = null;

    protected $_type = null;

    public function __construct($text = null, $relationship = null,  $type = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_relationship = $relationship;
        $this->_type = $type;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_relationship !== null) {
            $element->setAttribute('relationship', $this->_relationship);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'relationship':
            $this->_relationship = $attribute->nodeValue;
            break;
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getRelationship()
    {
        return $this->_relationship;
    }

    public function setRelationship($value)
    {
        $this->_relationship = $value;
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

}
