<?php


require_once('Zend/Gdata/Query.php');

require_once('Zend/Gdata/Gbase/Query.php');

class Zend_Gdata_Gbase_ItemQuery extends Zend_Gdata_Gbase_Query
{

    const GBASE_ITEM_FEED_URI = 'http://www.google.com/base/feeds/items';
    
    protected $_defaultFeedUri = self::GBASE_ITEM_FEED_URI;
    
    protected $_id = null;

    public function setId($value)
    {
        $this->_id = $value;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        if ($this->getId() !== null) {
            $uri .= '/' . $this->getId();
        } else {
            $uri .= $this->getQueryString();
        }
        return $uri;
    }

}
