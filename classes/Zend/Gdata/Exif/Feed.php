<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Exif.php';

require_once 'Zend/Gdata/Exif/Entry.php';

class Zend_Gdata_Exif_Feed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Exif_Entry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Exif::$namespaces);
        parent::__construct($element);
    }

}
