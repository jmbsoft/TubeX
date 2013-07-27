<?php


require_once 'Zend/Gdata/Books/Extension/BooksLink.php';

class Zend_Gdata_Books_Extension_ThumbnailLink extends
    Zend_Gdata_Books_Extension_BooksLink
{

    public function __construct($href = null, $rel = null, $type = null,
            $hrefLang = null, $title = null, $length = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($href, $rel, $type, $hrefLang, $title, $length);
    }

}
