<?php


require_once 'Zend/Gdata.php';

class Zend_Gdata_DublinCore extends Zend_Gdata
{

    public static $namespaces = array(
        array('dc', 'http://purl.org/dc/terms', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_DublinCore');
        $this->registerPackage('Zend_Gdata_DublinCore_Extension');
        parent::__construct($client, $applicationId);
    }

}
