<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Gapps/NicknameEntry.php';

class Zend_Gdata_Gapps_NicknameFeed extends Zend_Gdata_Feed
{
    
    protected $_entryClassName = 'Zend_Gdata_Gapps_NicknameEntry';
    protected $_feedClassName = 'Zend_Gdata_Gapps_NicknameFeed';
    
}
