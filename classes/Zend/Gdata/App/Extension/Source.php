<?php


require_once 'Zend/Gdata/App/Entry.php';

require_once 'Zend/Gdata/App/FeedSourceParent.php';

class Zend_Gdata_App_Extension_Source extends Zend_Gdata_App_FeedSourceParent
{

    protected $_rootElement = 'source';

}
