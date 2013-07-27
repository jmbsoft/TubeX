<?php


abstract class Zend_Search_Lucene_Search_Weight
{

    protected $_queryNorm;

    protected $_value;

    public function getValue()
    {
        return $this->_value;
    }

    abstract public function sumOfSquaredWeights();

    abstract public function normalize($norm);
}

