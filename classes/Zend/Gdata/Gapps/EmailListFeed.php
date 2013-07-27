<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Gapps/EmailListEntry.php';

class Zend_Gdata_Gapps_EmailListFeed extends Zend_Gdata_Feed
{
    
    protected $_entryClassName = 'Zend_Gdata_Gapps_EmailListEntry';
    protected $_feedClassName = 'Zend_Gdata_Gapps_EmailListFeed';
    
}
