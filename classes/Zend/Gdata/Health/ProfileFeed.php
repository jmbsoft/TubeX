<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Health_ProfileFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Health_ProfileEntry';

    public function __construct($element = null)
    {
        foreach (Zend_Gdata_Health::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct($element);
    }

    public function getEntries()
    {
        return $this->entry;
    }
}
