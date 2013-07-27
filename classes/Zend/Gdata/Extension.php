<?php


require_once 'Zend/Gdata/App/Extension.php';

class Zend_Gdata_Extension extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'gd';

    public function __construct()
    {

        $this->registerNamespace('gd',
                'http://schemas.google.com/g/2005');
        $this->registerNamespace('openSearch',
                'http://a9.com/-/spec/opensearchrss/1.0/', 1, 0);
        $this->registerNamespace('openSearch',
                'http://a9.com/-/spec/opensearch/1.1/', 2, 0);
        $this->registerNamespace('rss',
                'http://blogs.law.harvard.edu/tech/rss');

        parent::__construct();
    }

}
