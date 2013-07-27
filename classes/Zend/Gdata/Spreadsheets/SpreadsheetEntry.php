<?php


require_once 'Zend/Gdata/Entry.php';

class Zend_Gdata_Spreadsheets_SpreadsheetEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_SpreadsheetEntry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    public function getWorksheets()
    {
        $service = new Zend_Gdata_Spreadsheets($this->getHttpClient());
        return $service->getWorksheetFeed($this);
    }

}
