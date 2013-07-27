<?php


require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

require_once 'Zend/Search/Lucene/Index/SegmentWriter/StreamWriter.php';

require_once 'Zend/Search/Lucene/Index/SegmentInfoPriorityQueue.php';

class Zend_Search_Lucene_Index_SegmentMerger
{

    private $_writer;

    private $_docCount;

    private $_segmentInfos = array();

    private $_mergeDone = false;

    private $_fieldsMap = array();

    public function __construct($directory, $name)
    {
        $this->_writer = new Zend_Search_Lucene_Index_SegmentWriter_StreamWriter($directory, $name);
    }

    public function addSource(Zend_Search_Lucene_Index_SegmentInfo $segmentInfo)
    {
        $this->_segmentInfos[$segmentInfo->getName()] = $segmentInfo;
    }

    public function merge()
    {
        if ($this->_mergeDone) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Merge is already done.');
        }

        if (count($this->_segmentInfos) < 1) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong number of segments to be merged ('
                                                 . count($this->_segmentInfos)
                                                 . ').');
        }

        $this->_mergeFields();
        $this->_mergeNorms();
        $this->_mergeStoredFields();
        $this->_mergeTerms();

        $this->_mergeDone = true;

        return $this->_writer->close();
    }

    private function _mergeFields()
    {
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            foreach ($segmentInfo->getFieldInfos() as $fieldInfo) {
                $this->_fieldsMap[$segName][$fieldInfo->number] = $this->_writer->addFieldInfo($fieldInfo);
            }
        }
    }

    private function _mergeNorms()
    {
        foreach ($this->_writer->getFieldInfos() as $fieldInfo) {
            if ($fieldInfo->isIndexed) {
                foreach ($this->_segmentInfos as $segName => $segmentInfo) {
                    if ($segmentInfo->hasDeletions()) {
                        $srcNorm = $segmentInfo->normVector($fieldInfo->name);
                        $norm    = '';
                        $docs    = $segmentInfo->count();
                        for ($count = 0; $count < $docs; $count++) {
                            if (!$segmentInfo->isDeleted($count)) {
                                $norm .= $srcNorm[$count];
                            }
                        }
                        $this->_writer->addNorm($fieldInfo->name, $norm);
                    } else {
                        $this->_writer->addNorm($fieldInfo->name, $segmentInfo->normVector($fieldInfo->name));
                    }
                }
            }
        }
    }

    private function _mergeStoredFields()
    {
        $this->_docCount = 0;

        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $fdtFile = $segmentInfo->openCompoundFile('.fdt');

            for ($count = 0; $count < $segmentInfo->count(); $count++) {
                $fieldCount = $fdtFile->readVInt();
                $storedFields = array();

                for ($count2 = 0; $count2 < $fieldCount; $count2++) {
                    $fieldNum = $fdtFile->readVInt();
                    $bits = $fdtFile->readByte();
                    $fieldInfo = $segmentInfo->getField($fieldNum);

                    if (!($bits & 2)) { // Text data
                        $storedFields[] =
                                 new Zend_Search_Lucene_Field($fieldInfo->name,
                                                              $fdtFile->readString(),
                                                              'UTF-8',
                                                              true,
                                                              $fieldInfo->isIndexed,
                                                              $bits & 1 );
                    } else {            // Binary data
                        $storedFields[] =
                                 new Zend_Search_Lucene_Field($fieldInfo->name,
                                                              $fdtFile->readBinary(),
                                                              '',
                                                              true,
                                                              $fieldInfo->isIndexed,
                                                              $bits & 1,
                                                              true);
                    }
                }

                if (!$segmentInfo->isDeleted($count)) {
                    $this->_docCount++;
                    $this->_writer->addStoredFields($storedFields);
                }
            }
        }
    }

    private function _mergeTerms()
    {
        $segmentInfoQueue = new Zend_Search_Lucene_Index_SegmentInfoPriorityQueue();

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segName => $segmentInfo) {
            $segmentStartId = $segmentInfo->reset($segmentStartId, Zend_Search_Lucene_Index_SegmentInfo::SM_MERGE_INFO);

            // Skip "empty" segments
            if ($segmentInfo->currentTerm() !== null) {
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        $this->_writer->initializeDictionaryFiles();

        $termDocs = array();
        while (($segmentInfo = $segmentInfoQueue->pop()) !== null) {
            // Merge positions array
            $termDocs += $segmentInfo->currentTermPositions();

            if ($segmentInfoQueue->top() === null ||
                $segmentInfoQueue->top()->currentTerm()->key() !=
                            $segmentInfo->currentTerm()->key()) {
                // We got new term
                ksort($termDocs, SORT_NUMERIC);

                // Add term if it's contained in any document
                if (count($termDocs) > 0) {
                    $this->_writer->addTerm($segmentInfo->currentTerm(), $termDocs);
                }
                $termDocs = array();
            }

            $segmentInfo->nextTerm();
            // check, if segment dictionary is finished
            if ($segmentInfo->currentTerm() !== null) {
                // Put segment back into the priority queue
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        $this->_writer->closeDictionaryFiles();
    }
}
