<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Geo.php';

require_once 'Zend/Gdata/Geo/Entry.php';

class Zend_Gdata_Geo_Feed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Geo_Entry';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Geo::$namespaces);
        parent::__construct($element);
    }

}
