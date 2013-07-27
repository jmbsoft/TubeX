<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Geo.php';

class Zend_Gdata_Geo_Extension_GmlPos extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gml';
    protected $_rootElement = 'pos';

    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Geo::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
