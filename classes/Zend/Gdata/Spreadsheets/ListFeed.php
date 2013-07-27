<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Spreadsheets_ListFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_ListEntry';

    protected $_feedClassName = 'Zend_Gdata_Spreadsheets_ListFeed';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

}
