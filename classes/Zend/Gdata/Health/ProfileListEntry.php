<?php


require_once 'Zend/Gdata/Entry.php';

class Zend_Gdata_Health_ProfileListEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Health_ProfileListEntry';

    public function __construct($element = null)
    {
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        parent::takeChildFromDOM($child);
    }

    public function getProfileID() {
        return $this->getContent()->text;
    }

    public function getProfileName() {
        return $this->getTitle()->text;
    }

}
