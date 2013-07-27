<?php


require_once 'Zend/Gdata/Media/Feed.php';

require_once 'Zend/Gdata/YouTube/InboxEntry.php';

class Zend_Gdata_YouTube_InboxFeed extends Zend_Gdata_Media_Feed
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_InboxEntry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

}
