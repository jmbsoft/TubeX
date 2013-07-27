<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Exif.php';

class Zend_Gdata_Exif_Extension_FocalLength extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'exif';
    protected $_rootElement = 'focallength';

    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Exif::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
