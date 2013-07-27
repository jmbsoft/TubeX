<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Media.php';

require_once 'Zend/Gdata/Media/Entry.php';

class Zend_Gdata_Media_Feed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Media_Entry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct($element);
    }

}
