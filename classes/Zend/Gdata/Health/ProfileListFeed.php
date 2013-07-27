<?php


require_once 'Zend/Gdata/Feed.php';

class Zend_Gdata_Health_ProfileListFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Health_ProfileListEntry';
    
    public function getEntries()
    {
        return $this->entry;
    }
}
