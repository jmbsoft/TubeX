<?php


require_once('Zend/Gdata/Query.php');

require_once('Zend/Gdata/Gbase/Query.php');

class Zend_Gdata_Gbase_SnippetQuery extends Zend_Gdata_Gbase_Query
{

    const BASE_SNIPPET_FEED_URI = 'http://www.google.com/base/feeds/snippets';
    
    protected $_defaultFeedUri = self::BASE_SNIPPET_FEED_URI;

    public function getQueryUrl()
    {
        $uri = $this->_defaultFeedUri;
        if ($this->getCategory() !== null) {
            $uri .= '/-/' . $this->getCategory();
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
