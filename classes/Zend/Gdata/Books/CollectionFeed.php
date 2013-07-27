<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Books_CollectionFeed extends Zend_Gdata_Feed
{

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($element);
    }

    protected $_entryClassName = 'Zend_Gdata_Books_CollectionEntry';

}

