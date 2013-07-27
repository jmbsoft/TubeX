<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Media_Extension_MediaKeywords extends Zend_Gdata_Extension
{
    protected $_rootElement = 'keywords';
    protected $_rootNamespace = 'media';

    public function __construct()
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
    }

}
