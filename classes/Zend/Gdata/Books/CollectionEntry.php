<?php


require_once 'Zend/Gdata/Entry.php';

class Zend_Gdata_Books_CollectionEntry extends Zend_Gdata_Entry
{

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct($element);
    }


}

