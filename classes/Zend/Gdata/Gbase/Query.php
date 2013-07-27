<?php


require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Gbase_Query extends Zend_Gdata_Query
{

    const GBASE_ITEM_FEED_URI = 'http://www.google.com/base/feeds/items';

    const GBASE_SNIPPET_FEED_URI = 'http://www.google.com/base/feeds/snippets';
    
    protected $_defaultFeedUri = self::GBASE_ITEM_FEED_URI;

    public function setKey($value)
    {
        if ($value !== null) {
            $this->_params['key'] = $value;
        } else {
            unset($this->_params['key']);
        }
        return $this;
    }

    public function setBq($value)
    {
        if ($value !== null) {
            $this->_params['bq'] = $value;
        } else {
            unset($this->_params['bq']);
        }
        return $this;
    }

    public function setRefine($value)
    {
        if ($value !== null) {
            $this->_params['refine'] = $value;
        } else {
            unset($this->_params['refine']);
        }
        return $this;
    }

    public function setContent($value)
    {
        if ($value !== null) {
            $this->_params['content'] = $value;
        } else {
            unset($this->_params['content']);
        }
        return $this;
    }

    public function setOrderBy($value)
    {
        if ($value !== null) {
            $this->_params['orderby'] = $value;
        } else {
            unset($this->_params['orderby']);
        }
        return $this;
    }

    public function setSortOrder($value)
    {
        if ($value !== null) {
            $this->_params['sortorder'] = $value;
        } else {
            unset($this->_params['sortorder']);
        }
        return $this;
    }

    public function setCrowdBy($value)
    {
        if ($value !== null) {
            $this->_params['crowdby'] = $value;
        } else {
            unset($this->_params['crowdby']);
        }
        return $this;
    }

    public function setAdjust($value)
    {
        if ($value !== null) {
            $this->_params['adjust'] = $value;
        } else {
            unset($this->_params['adjust']);
        }
        return $this;
    }

    public function getKey()
    {
        if (array_key_exists('key', $this->_params)) {
            return $this->_params['key'];
        } else {
            return null;
        }
    }

    public function getBq()
    {
        if (array_key_exists('bq', $this->_params)) {
            return $this->_params['bq'];
        } else {
            return null;
        }
    }

    public function getRefine()
    {
        if (array_key_exists('refine', $this->_params)) {
            return $this->_params['refine'];
        } else {
            return null;
        }
    }

    public function getContent()
    {
        if (array_key_exists('content', $this->_params)) {
            return $this->_params['content'];
        } else {
            return null;
        }
    }

    public function getOrderBy()
    {
        if (array_key_exists('orderby', $this->_params)) {
            return $this->_params['orderby'];
        } else {
            return null;
        }
    }

    public function getSortOrder()
    {
        if (array_key_exists('sortorder', $this->_params)) {
            return $this->_params['sortorder'];
        } else {
            return null;
        }
    }

    public function getCrowdBy()
    {
        if (array_key_exists('crowdby', $this->_params)) {
            return $this->_params['crowdby'];
        } else {
            return null;
        }
    }

    public function getAdjust()
    {
        if (array_key_exists('adjust', $this->_params)) {
            return $this->_params['adjust'];
        } else {
            return null;
        }
    }

}
