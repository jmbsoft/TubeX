<?php


require_once('Zend/Gdata/App/Util.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Spreadsheets_ListQuery extends Zend_Gdata_Query
{

    const SPREADSHEETS_LIST_FEED_URI = 'http://spreadsheets.google.com/feeds/list';

    protected $_defaultFeedUri = self::SPREADSHEETS_LIST_FEED_URI;
    protected $_visibility = 'private';
    protected $_projection = 'full';
    protected $_spreadsheetKey = null;
    protected $_worksheetId = 'default';
    protected $_rowId = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function setSpreadsheetKey($value)
    {
        $this->_spreadsheetKey = $value;
        return $this;
    }

    public function getSpreadsheetKey()
    {
        return $this->_spreadsheetKey;
    }

    public function setWorksheetId($value)
    {
        $this->_worksheetId = $value;
        return $this;
    }

    public function getWorksheetId()
    {
        return $this->_worksheetId;
    }

    public function setRowId($value)
    {
        $this->_rowId = $value;
        return $this;
    }

    public function getRowId()
    {
        return $this->_rowId;
    }

    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }

    public function getProjection()
    {
        return $this->_projection;
    }

    public function getVisibility()
    {
        return $this->_visibility;
    }

    public function setSpreadsheetQuery($value)
    {
        if ($value != null) {
            $this->_params['sq'] = $value;
        } else {
            unset($this->_params['sq']);
        }
        return $this;
    }

    public function getSpreadsheetQuery()
    {
        if (array_key_exists('sq', $this->_params)) {
            return $this->_params['sq'];
        } else {
            return null;
        }
    }

    public function setOrderBy($value)
    {
        if ($value != null) {
            $this->_params['orderby'] = $value;
        } else {
            unset($this->_params['orderby']);
        }
        return $this;
    }

    public function getOrderBy()
    {
        if (array_key_exists('orderby', $this->_params)) {
            return $this->_params['orderby'];
        } else {
            return null;
        }
    }

    public function setReverse($value)
    {
        if ($value != null) {
            $this->_params['reverse'] = $value;
        } else {
            unset($this->_params['reverse']);
        }
        return $this;
    }

    public function getReverse()
    {


        if (array_key_exists('reverse', $this->_params)) {
            return $this->_params['reverse'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {

        $uri = $this->_defaultFeedUri;

        if ($this->_spreadsheetKey != null) {
            $uri .= '/'.$this->_spreadsheetKey;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A spreadsheet key must be provided for list queries.');
        }

        if ($this->_worksheetId != null) {
            $uri .= '/'.$this->_worksheetId;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A worksheet id must be provided for list queries.');
        }

        if ($this->_visibility != null) {
            $uri .= '/'.$this->_visibility;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A visibility must be provided for list queries.');
        }

        if ($this->_projection != null) {
            $uri .= '/'.$this->_projection;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A projection must be provided for list queries.');
        }

        if ($this->_rowId != null) {
            $uri .= '/'.$this->_rowId;
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

    public function getQueryString()
    {
        return parent::getQueryString();
    }

}
