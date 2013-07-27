<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Gapps/EmailListRecipientEntry.php';

class Zend_Gdata_Gapps_EmailListRecipientFeed extends Zend_Gdata_Feed
{
    
    protected $_entryClassName = 'Zend_Gdata_Gapps_EmailListRecipientEntry';
    protected $_feedClassName = 'Zend_Gdata_Gapps_EmailListRecipientFeed';
    
}
