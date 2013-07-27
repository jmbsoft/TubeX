<?php


class Zend_Search_Lucene_Search_QueryHit
{

    protected $_index = null;

    protected $_document = null;

    public $id;

    public $score;


    public function __construct(Zend_Search_Lucene_Interface $index)
    {
        $this->_index = new Zend_Search_Lucene_Proxy($index);
    }

    public function __get($offset)
    {
        return $this->getDocument()->getFieldValue($offset);
    }

    public function getDocument()
    {
        if (!$this->_document instanceof Zend_Search_Lucene_Document) {
            $this->_document = $this->_index->getDocument($this->id);
        }

        return $this->_document;
    }

    public function getIndex()
    {
        return $this->_index;
    }
}

