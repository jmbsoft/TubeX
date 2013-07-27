<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Gapps/UserEntry.php';

class Zend_Gdata_Gapps_UserFeed extends Zend_Gdata_Feed
{
    
    protected $_entryClassName = 'Zend_Gdata_Gapps_UserEntry';
    protected $_feedClassName = 'Zend_Gdata_Gapps_UserFeed';
    
}
