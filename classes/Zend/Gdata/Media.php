<?php


require_once 'Zend/Gdata.php';

class Zend_Gdata_Media extends Zend_Gdata
{

    public static $namespaces = array(
        array('media', 'http://search.yahoo.com/mrss/', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Media');
        $this->registerPackage('Zend_Gdata_Media_Extension');
        parent::__construct($client, $applicationId);
    }

}
