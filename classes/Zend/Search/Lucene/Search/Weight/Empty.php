<?php


require_once 'Zend/Search/Lucene/Search/Weight.php';

class Zend_Search_Lucene_Search_Weight_Empty extends Zend_Search_Lucene_Search_Weight
{

    public function sumOfSquaredWeights()
    {
        return 1;
    }

    public function normalize($queryNorm)
    {
    }
}

