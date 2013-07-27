<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Photos/Extension/Id.php';

require_once 'Zend/Gdata/Photos/Extension/PhotoId.php';

require_once 'Zend/Gdata/Photos/Extension/Weight.php';

require_once 'Zend/Gdata/App/Extension/Category.php';

class Zend_Gdata_Photos_CommentEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_CommentEntry';

    protected $_gphotoId = null;

    protected $_gphotoPhotoId = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#comment',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoId !== null) {
            $element->appendChild($this->_gphotoId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoPhotoId !== null) {
            $element->appendChild($this->_gphotoPhotoId->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'id';
                $id = new Zend_Gdata_Photos_Extension_Id();
                $id->transferFromDOM($child);
                $this->_gphotoId = $id;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'photoid';
                $photoid = new Zend_Gdata_Photos_Extension_PhotoId();
                $photoid->transferFromDOM($child);
                $this->_gphotoPhotoId = $photoid;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getGphotoPhotoId()
    {
        return $this->_gphotoPhotoId;
    }

    public function setGphotoPhotoId($value)
    {
        $this->_gphotoPhotoId = $value;
        return $this;
    }

    public function getGphotoId()
    {
        return $this->_gphotoId;
    }

    public function setGphotoId($value)
    {
        $this->_gphotoId = $value;
        return $this;
    }
}
