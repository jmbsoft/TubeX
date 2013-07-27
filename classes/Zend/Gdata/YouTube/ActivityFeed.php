<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/YouTube/ActivityEntry.php';

class Zend_Gdata_YouTube_ActivityFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_ActivityEntry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

}
