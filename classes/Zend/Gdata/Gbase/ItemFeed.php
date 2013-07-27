<?php


require_once 'Zend/Gdata/Gbase/Feed.php';

class Zend_Gdata_Gbase_ItemFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Gbase_ItemEntry';
}
