<?php


require_once 'Zend/Gdata.php';

class Zend_Gdata_Exif extends Zend_Gdata
{

    public static $namespaces = array(
        array('exif', 'http://schemas.google.com/photos/exif/2007', 1, 0)
    );

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Exif');
        $this->registerPackage('Zend_Gdata_Exif_Extension');
        parent::__construct($client, $applicationId);
    }

}
