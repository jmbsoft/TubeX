<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Gbase_Feed extends Zend_Gdata_Feed
{

    protected $_feedClassName = 'Zend_Gdata_Gbase_Feed';

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gbase::$namespaces);
        parent::__construct($element);
    }
}
