<?php


require_once 'Zend/Gdata/App/Extension/Link.php';

class Zend_Gdata_Books_Extension_BooksLink extends Zend_Gdata_App_Extension_Link
{

    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($href, $rel, $type, $hrefLang, $title, $length);
    }


}

