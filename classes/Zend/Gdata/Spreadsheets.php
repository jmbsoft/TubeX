<?php


require_once('Zend/Gdata.php');

require_once('Zend/Gdata/Spreadsheets/SpreadsheetFeed.php');

require_once('Zend/Gdata/Spreadsheets/WorksheetFeed.php');

require_once('Zend/Gdata/Spreadsheets/CellFeed.php');

require_once('Zend/Gdata/Spreadsheets/ListFeed.php');

require_once('Zend/Gdata/Spreadsheets/SpreadsheetEntry.php');

require_once('Zend/Gdata/Spreadsheets/WorksheetEntry.php');

require_once('Zend/Gdata/Spreadsheets/CellEntry.php');

require_once('Zend/Gdata/Spreadsheets/ListEntry.php');

require_once('Zend/Gdata/Spreadsheets/DocumentQuery.php');

require_once('Zend/Gdata/Spreadsheets/ListQuery.php');

require_once('Zend/Gdata/Spreadsheets/CellQuery.php');

class Zend_Gdata_Spreadsheets extends Zend_Gdata
{
    const SPREADSHEETS_FEED_URI = 'http://spreadsheets.google.com/feeds/spreadsheets';
    const SPREADSHEETS_POST_URI = 'http://spreadsheets.google.com/feeds/spreadsheets/private/full';
    const WORKSHEETS_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#worksheetsfeed';
    const LIST_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#listfeed';
    const CELL_FEED_LINK_URI = 'http://schemas.google.com/spreadsheets/2006#cellsfeed';
    const AUTH_SERVICE_NAME = 'wise';

    public static $namespaces = array(
        array('gs', 'http://schemas.google.com/spreadsheets/2006', 1, 0),
        array(
            'gsx', 'http://schemas.google.com/spreadsheets/2006/extended', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Spreadsheets');
        $this->registerPackage('Zend_Gdata_Spreadsheets_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
        $this->_server = 'spreadsheets.google.com';
    }

    public function getSpreadsheetFeed($location = null)
    {
        if ($location == null) {
            $uri = self::SPREADSHEETS_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('spreadsheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_SpreadsheetFeed');
    }

    public function getSpreadsheetEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('spreadsheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_SpreadsheetEntry');
    }

    public function getWorksheetFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('worksheets');
            }
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_SpreadsheetEntry) {
            $uri = $location->getLink(self::WORKSHEETS_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_WorksheetFeed');
    }

    public function GetWorksheetEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_DocumentQuery) {
            if ($location->getDocumentType() == null) {
                $location->setDocumentType('worksheets');
            }
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_WorksheetEntry');
    }

    public function getCellFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $uri = $location->getLink(self::CELL_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_CellFeed');
    }

    public function getCellEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_CellEntry');
    }

    public function getListFeed($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_ListQuery) {
            $uri = $location->getQueryUrl();
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $uri = $location->getLink(self::LIST_FEED_LINK_URI)->href;
        } else {
            $uri = $location;
        }

        return parent::getFeed($uri, 'Zend_Gdata_Spreadsheets_ListFeed');
    }

    public function getListEntry($location)
    {
        if ($location instanceof Zend_Gdata_Spreadsheets_ListQuery) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }

        return parent::getEntry($uri, 'Zend_Gdata_Spreadsheets_ListEntry');
    }

    public function updateCell($row, $col, $inputValue, $key, $wkshtId = 'default')
    {
        $cell = 'R'.$row.'C'.$col;

        $query = new Zend_Gdata_Spreadsheets_CellQuery();
        $query->setSpreadsheetKey($key);
        $query->setWorksheetId($wkshtId);
        $query->setCellId($cell);

        $entry = $this->getCellEntry($query);
        $entry->setCell(new Zend_Gdata_Spreadsheets_Extension_Cell(null, $row, $col, $inputValue));
        $response = $entry->save();
        return $response;
    }

    public function insertRow($rowData, $key, $wkshtId = 'default')
    {
        $newEntry = new Zend_Gdata_Spreadsheets_ListEntry();
        $newCustomArr = array();
        foreach ($rowData as $k => $v) {
            $newCustom = new Zend_Gdata_Spreadsheets_Extension_Custom();
            $newCustom->setText($v)->setColumnName($k);
            $newEntry->addCustom($newCustom);
        }

        $query = new Zend_Gdata_Spreadsheets_ListQuery();
        $query->setSpreadsheetKey($key);
        $query->setWorksheetId($wkshtId);

        $feed = $this->getListFeed($query);
        $editLink = $feed->getLink('http://schemas.google.com/g/2005#post');

        return $this->insertEntry($newEntry->saveXML(), $editLink->href, 'Zend_Gdata_Spreadsheets_ListEntry');
    }

    public function updateRow($entry, $newRowData)
    {
        $newCustomArr = array();
        foreach ($newRowData as $k => $v) {
            $newCustom = new Zend_Gdata_Spreadsheets_Extension_Custom();
            $newCustom->setText($v)->setColumnName($k);
            $newCustomArr[] = $newCustom;
        }
        $entry->setCustom($newCustomArr);

        return $entry->save();
    }

    public function deleteRow($entry)
    {
        $entry->delete();
    }

    public function getSpreadsheetListFeedContents($location)
    {
        $listFeed = $this->getListFeed($location);
        $listFeed = $this->retrieveAllEntriesForFeed($listFeed);
        $spreadsheetContents = array();
        foreach ($listFeed as $listEntry) {
            $rowContents = array();
            $customArray = $listEntry->getCustom();
            foreach ($customArray as $custom) {
                $rowContents[$custom->getColumnName()] = $custom->getText();
            }
            $spreadsheetContents[] = $rowContents;
        }
        return $spreadsheetContents;
    }

    public function getSpreadsheetCellFeedContents($location, $range = null, $empty = false)
    {
        $cellQuery = null;
        if ($location instanceof Zend_Gdata_Spreadsheets_CellQuery) {
            $cellQuery = $location;
        } else if ($location instanceof Zend_Gdata_Spreadsheets_WorksheetEntry) {
            $url = $location->getLink(self::CELL_FEED_LINK_URI)->href;
            $cellQuery = new Zend_Gdata_Spreadsheets_CellQuery($url);
        } else {
            $url = $location;
            $cellQuery = new Zend_Gdata_Spreadsheets_CellQuery($url);
        }

        if ($range != null) {
            $cellQuery->setRange($range);
        }
        $cellQuery->setReturnEmpty($empty);

        $cellFeed = $this->getCellFeed($cellQuery);
        $cellFeed = $this->retrieveAllEntriesForFeed($cellFeed);
        $spreadsheetContents = array();
        foreach ($cellFeed as $cellEntry) {
            $cellContents = array();
            $cell = $cellEntry->getCell();
            $cellContents['formula'] = $cell->getInputValue();
            $cellContents['value'] = $cell->getText();
            $spreadsheetContents[$cellEntry->getTitle()->getText()] = $cellContents;
        }
        return $spreadsheetContents;
    }

    public function getSpreadsheets($location = null)
    {
        return $this->getSpreadsheetFeed($location = null);
    }

}
