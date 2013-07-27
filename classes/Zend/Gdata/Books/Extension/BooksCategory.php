<?php


require_once 'Zend/Gdata/App/Extension/Category.php';

class Zend_Gdata_Books_Extension_BooksCategory extends
    Zend_Gdata_App_Extension_Category
{

    public function __construct($term = null, $scheme = null, $label = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($term, $scheme, $label);
    }

}
