<?php


require_once 'Zend/Gdata/Entry.php';

class Zend_Gdata_Docs_DocumentListEntry extends Zend_Gdata_Entry
{

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Docs::$namespaces);
        parent::__construct($element);
    }

}
