<?php


require_once 'Zend/Gdata.php';

class Zend_Gdata_Geo extends Zend_Gdata
{

    public static $namespaces = array(
        array('georss', 'http://www.georss.org/georss', 1, 0),
        array('gml', 'http://www.opengis.net/gml', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Geo');
        $this->registerPackage('Zend_Gdata_Geo_Extension');
        parent::__construct($client, $applicationId);
    }

}
