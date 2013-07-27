<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/YouTube/CommentEntry.php';

class Zend_Gdata_YouTube_CommentFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_CommentEntry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

}
