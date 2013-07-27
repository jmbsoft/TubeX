<?php


require_once('Zend/Gdata/App/Util.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Spreadsheets_DocumentQuery extends Zend_Gdata_Query
{

    const SPREADSHEETS_FEED_URI = 'http://spreadsheets.google.com/feeds';

    protected $_defaultFeedUri = self::SPREADSHEETS_FEED_URI;
    protected $_documentType;
    protected $_visibility = 'private';
    protected $_projection = 'full';
    protected $_spreadsheetKey = null;
    protected $_worksheetId = null;

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

    public function setDocumentType($value)
    {
        $this->_documentType = $value;
        return $this;
    }

    public function getDocumentType()
    {
        return $this->_documentType;
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

    public function setTitle($value)
    {
        if ($value != null) {
            $this->_params['title'] = $value;
        } else {
            unset($this->_params['title']);
        }
        return $this;
    }

    public function setTitleExact($value)
    {
        if ($value != null) {
            $this->_params['title-exact'] = $value;
        } else {
            unset($this->_params['title-exact']);
        }
        return $this;
    }

    public function getTitle()
    {
        if (array_key_exists('title', $this->_params)) {
            return $this->_params['title'];
        } else {
            return null;
        }
    }

    public function getTitleExact()
    {
        if (array_key_exists('title-exact', $this->_params)) {
            return $this->_params['title-exact'];
        } else {
            return null;
        }
    }

    private function appendVisibilityProjection()
    {
        $uri = '';

        if ($this->_visibility != null) {
            $uri .= '/'.$this->_visibility;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A visibility must be provided for document queries.');
        }

        if ($this->_projection != null) {
            $uri .= '/'.$this->_projection;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A projection must be provided for document queries.');
        }

        return $uri;
    }

    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;

        if ($this->_documentType != null) {
            $uri .= '/'.$this->_documentType;
        } else {
            require_once 'Zend/Gdata/App/Exception.php'; 
            throw new Zend_Gdata_App_Exception('A document type must be provided for document queries.');
        }

        if ($this->_documentType == 'spreadsheets') {
            $uri .= $this->appendVisibilityProjection();
            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            }
        } else if ($this->_documentType == 'worksheets') {
            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            } else {
                require_once 'Zend/Gdata/App/Exception.php'; 
                throw new Zend_Gdata_App_Exception('A spreadsheet key must be provided for worksheet document queries.');
            }
            $uri .= $this->appendVisibilityProjection();
            if ($this->_worksheetId != null) {
                $uri .= '/'.$this->_worksheetId;
            }
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

    public function getQueryString()
    {
        return parent::getQueryString();
    }

}
