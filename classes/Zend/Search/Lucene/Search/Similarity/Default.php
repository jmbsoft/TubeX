<?php


require_once 'Zend/Search/Lucene/Search/Similarity.php';

class Zend_Search_Lucene_Search_Similarity_Default extends Zend_Search_Lucene_Search_Similarity
{

    public function lengthNorm($fieldName, $numTerms)
    {
        if ($numTerms == 0) {
            return 1E10;
        }

        return 1.0/sqrt($numTerms);
    }

    public function queryNorm($sumOfSquaredWeights)
    {
        return 1.0/sqrt($sumOfSquaredWeights);
    }

    public function tf($freq)
    {
        return sqrt($freq);
    }

    public function sloppyFreq($distance)
    {
        return 1.0/($distance + 1);
    }

    public function idfFreq($docFreq, $numDocs)
    {
        return log($numDocs/(float)($docFreq+1)) + 1.0;
    }

    public function coord($overlap, $maxOverlap)
    {
        return $overlap/(float)$maxOverlap;
    }
}
