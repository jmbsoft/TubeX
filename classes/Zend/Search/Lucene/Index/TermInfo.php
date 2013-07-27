<?php


class Zend_Search_Lucene_Index_TermInfo
{

    public $docFreq;

    public $freqPointer;

    public $proxPointer;

    public $skipOffset;

    public $indexPointer;

    public function __construct($docFreq, $freqPointer, $proxPointer, $skipOffset, $indexPointer = null)
    {
        $this->docFreq      = $docFreq;
        $this->freqPointer  = $freqPointer;
        $this->proxPointer  = $proxPointer;
        $this->skipOffset   = $skipOffset;
        $this->indexPointer = $indexPointer;
    }
}

