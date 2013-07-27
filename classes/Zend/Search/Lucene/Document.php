<?php


require_once 'Zend/Search/Lucene/Field.php';

class Zend_Search_Lucene_Document
{

    protected $_fields = array();

    public $boost = 1.0;

    public function __get($offset)
    {
        return $this->getFieldValue($offset);
    }

    public function addField(Zend_Search_Lucene_Field $field)
    {
        $this->_fields[$field->name] = $field;

        return $this;
    }

    public function getFieldNames()
    {
        return array_keys($this->_fields);
    }

    public function getField($fieldName)
    {
        if (!array_key_exists($fieldName, $this->_fields)) {
            throw new Zend_Search_Lucene_Exception("Field name \"$fieldName\" not found in document.");
        }
        return $this->_fields[$fieldName];
    }

    public function getFieldValue($fieldName)
    {
        return $this->getField($fieldName)->value;
    }

    public function getFieldUtf8Value($fieldName)
    {
        return $this->getField($fieldName)->getUtf8Value();
    }
}
