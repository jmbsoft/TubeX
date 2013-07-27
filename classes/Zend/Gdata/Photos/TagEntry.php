<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Photos/Extension/Weight.php';

require_once 'Zend/Gdata/App/Extension/Category.php';

class Zend_Gdata_Photos_TagEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_TagEntry';

    protected $_gphotoWeight = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#tag',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoWeight !== null) {
            $element->appendChild($this->_gphotoWeight->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'weight';
                $weight = new Zend_Gdata_Photos_Extension_Weight();
                $weight->transferFromDOM($child);
                $this->_gphotoWeight = $weight;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getGphotoWeight()
    {
        return $this->_gphotoWeight;
    }

    public function setGphotoWeight($value)
    {
        $this->_gphotoWeight = $value;
        return $this;
    }
}
