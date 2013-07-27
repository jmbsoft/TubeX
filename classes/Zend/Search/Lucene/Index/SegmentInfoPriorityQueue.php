<?php


require_once 'Zend/Search/Lucene/PriorityQueue.php';

class Zend_Search_Lucene_Index_SegmentInfoPriorityQueue extends Zend_Search_Lucene_PriorityQueue
{

    protected function _less($segmentInfo1, $segmentInfo2)
    {
        return strcmp($segmentInfo1->currentTerm()->key(), $segmentInfo2->currentTerm()->key()) < 0;
    }

}
