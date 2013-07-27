<?php


require_once('Zend/Gdata/App/Util.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Spreadsheets_CellQuery extends Zend_Gdata_Query
{

    const SPREADSHEETS_CELL_FEED_URI = 'http://spreadsheets.google.com/feeds/cells';

    protected $_defaultFeedUri = self::SPREADSHEETS_CELL_FEED_URI;
    protected $_visibility = 'private';
    protected $_projection = 'full';
    protected $_spreadsheetKey = null;
    protected $_worksheetId = 'default';
    protected $_cellId = null;

    public function __construct($url = null)
    {
        parent::__construct($url);
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

    public function setCellId($value)
    {
        $this->_cellId = $value;
        return $this;
    }

    public function getCellId()
    {
        return $this->_cellId;
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

    public function setMinRow($value)
    {
        if ($value != null) {
            $this->_params['min-row'] = $value;
        } else {
            unset($this->_params['min-row']);
        }
        return $this;
    }

    public function getMinRow()
    {
        if (array_key_exists('min-row', $this->_params)) {
            return $this->_params['min-row'];
        } else {
            return null;
        }
    }

    public function setMaxRow($value)
    {
        if ($value != null) {
            $this->_params['max-row'] = $value;
        } else {
            unset($this->_params['max-row']);
        }
        return $this;
    }

    public function getMaxRow()
    {
        if (array_key_exists('max-row', $this->_params)) {
            return $this->_params['max-row'];
        } else {
            return null;
        }
    }

    public function setMinCol($value)
    {
        if ($value != null) {
            $this->_params['min-col'] = $value;
        } else {
            unset($this->_params['min-col']);
        }
        return $this;
    }

    public function getMinCol()
    {
        if (array_key_exists('min-col', $this->_params)) {
            return $this->_params['min-col'];
        } else {
            return null;
        }
    }

    public function setMaxCol($value)
    {
        if ($value != null) {
            $this->_params['max-col'] = $value;
        } else {
            unset($this->_params['max-col']);
        }
        return $this;
    }

    public function getMaxCol()
    {
        if (array_key_exists('max-col', $this->_params)) {
            return $this->_params['max-col'];
        } else {
            return null;
        }
    }

    public function setRange($value)
    {
        if ($value != null) {
            $this->_params['range'] = $value;
        } else {
            unset($this->_params['range']);
        }
        return $this;
    }

    public function getRange()
    {
        if (array_key_exists('range', $this->_params)) {
            return $this->_params['range'];
        } else {
            return null;
        }
    }

    public function setReturnEmpty($value)
    {
        if (is_bool($value)) {
            $this->_params['return-empty'] = ($value?'true':'false');
        } else if ($value != null) {
            $this->_params['return-empty'] = $value;
        } else {
            unset($this->_params['return-empty']);
        }
        return $this;
    }

    public function getReturnEmpty()
    {
        if (array_key_exists('return-empty', $this->_params)) {
            return $this->_params['return-empty'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {
        if ($this->_url == null) {
            $uri = $this->_defaultFeedUri;

            if ($this->_spreadsheetKey != null) {
                $uri .= '/'.$this->_spreadsheetKey;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A spreadsheet key must be provided for cell queries.');
            }

            if ($this->_worksheetId != null) {
                $uri .= '/'.$this->_worksheetId;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A worksheet id must be provided for cell queries.');
            }

            if ($this->_visibility != null) {
                $uri .= '/'.$this->_visibility;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A visibility must be provided for cell queries.');
            }

            if ($this->_projection != null) {
                $uri .= '/'.$this->_projection;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('A projection must be provided for cell queries.');
            }

            if ($this->_cellId != null) {
                $uri .= '/'.$this->_cellId;
            }
        } else {
            $uri = $this->_url;
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

    public function getQueryString()
    {
        return parent::getQueryString();
    }

}
