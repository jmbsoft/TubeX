<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Docs_DocumentListFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Docs_DocumentListEntry';

    protected $_feedClassName = 'Zend_Gdata_Docs_DocumentListFeed';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Docs::$namespaces);
        parent::__construct($element);
    }

}
