<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Spreadsheets_WorksheetFeed extends Zend_Gdata_Feed
{

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_WorksheetEntry';

    protected $_feedClassName = 'Zend_Gdata_Spreadsheets_WorksheetFeed';

}
