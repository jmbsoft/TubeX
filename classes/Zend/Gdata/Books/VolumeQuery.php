<?php


require_once('Zend/Gdata/Books.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Books_VolumeQuery extends Zend_Gdata_Query
{

    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    public function setMinViewability($value = null)
    {
        switch ($value) {
            case 'full_view':
                $this->_params['min-viewability'] = 'full';
                break;
            case 'partial_view':
                $this->_params['min-viewability'] = 'partial';
                break;
            case null:
                unset($this->_params['min-viewability']);
                break;
        }
        return $this;
    }

    public function getMinViewability()
    {
        if (array_key_exists('min-viewability', $this->_params)) {
            return $this->_params['min-viewability'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {
        if (isset($this->_url)) {
            $url = $this->_url;
        } else {
            $url = Zend_Gdata_Books::VOLUME_FEED_URI;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url = $url . $this->getQueryString();
        return $url;
    }

}
