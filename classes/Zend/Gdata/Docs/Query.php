<?php


require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Docs_Query extends Zend_Gdata_Query
{

    const DOCUMENTS_LIST_FEED_URI = 'http://docs.google.com/feeds/documents';

    protected $_defaultFeedUri = self::DOCUMENTS_LIST_FEED_URI;

    protected $_visibility = 'private';

    protected $_projection = 'full';

    public function __construct()
    {
        parent::__construct();
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
        if ($value !== null) {
            $this->_params['title'] = $value;
        } else {
            unset($this->_params['title']);
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

    public function setTitleExact($value)
    {
        if ($value) {
            $this->_params['title-exact'] = $value;
        } else {
            unset($this->_params['title-exact']);
        }
        return $this;
    }

    public function getTitleExact()
    {
        if (array_key_exists('title-exact', $this->_params)) {
            return $this->_params['title-exact'];
        } else {
            return false;
        }
    }

    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;

        if ($this->_visibility !== null) {
            $uri .= '/' . $this->_visibility;
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'A visibility must be provided for cell queries.');
        }

        if ($this->_projection !== null) {
            $uri .= '/' . $this->_projection;
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'A projection must be provided for cell queries.');
        }

        $uri .= $this->getQueryString();
        return $uri;
    }

}
