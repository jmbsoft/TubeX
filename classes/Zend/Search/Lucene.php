<?php


require_once 'Zend/Search/Lucene/Document.php';

require_once 'Zend/Search/Lucene/Document/Html.php';

require_once 'Zend/Search/Lucene/Document/Docx.php';

require_once 'Zend/Search/Lucene/Document/Pptx.php';

require_once 'Zend/Search/Lucene/Document/Xlsx.php';

require_once 'Zend/Search/Lucene/Storage/Directory/Filesystem.php';

require_once 'Zend/Search/Lucene/Storage/File/Memory.php';

require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Index/TermInfo.php';

require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

require_once 'Zend/Search/Lucene/Index/FieldInfo.php';

require_once 'Zend/Search/Lucene/Index/Writer.php';

require_once 'Zend/Search/Lucene/Search/QueryParser.php';

require_once 'Zend/Search/Lucene/Search/QueryHit.php';

require_once 'Zend/Search/Lucene/Search/Similarity.php';

require_once 'Zend/Search/Lucene/Index/SegmentInfoPriorityQueue.php';

require_once 'Zend/Search/Lucene/Index/DocsFilter.php';

require_once 'Zend/Search/Lucene/LockManager.php';

require_once 'Zend/Search/Lucene/Interface.php';

require_once 'Zend/Search/Lucene/Proxy.php';

class Zend_Search_Lucene implements Zend_Search_Lucene_Interface
{

    private static $_defaultSearchField = null;

    private static $_resultSetLimit = 0;

    private static $_termsPerQueryLimit = 1024;

    private $_directory = null;

    private $_closeDirOnExit = true;

    private $_writer = null;

    private $_segmentInfos = array();

    private $_docCount = 0;

    private $_hasChanges = false;

    private $_closed = false;

    private $_refCount = 0;

    private $_generation;

    const FORMAT_PRE_2_1 = 0;
    const FORMAT_2_1     = 1;
    const FORMAT_2_3     = 2;

    private $_formatVersion;

    public static function create($directory)
    {
        return new Zend_Search_Lucene_Proxy(new Zend_Search_Lucene($directory, true));
    }

    public static function open($directory)
    {
        return new Zend_Search_Lucene_Proxy(new Zend_Search_Lucene($directory, false));
    }

    const GENERATION_RETRIEVE_COUNT = 10;

    const GENERATION_RETRIEVE_PAUSE = 50;

    public static function getActualGeneration(Zend_Search_Lucene_Storage_Directory $directory)
    {


        require_once 'Zend/Search/Lucene/Exception.php';
        try {
            for ($count = 0; $count < self::GENERATION_RETRIEVE_COUNT; $count++) {
                // Try to get generation file
                $genFile = $directory->getFileObject('segments.gen', false);

                $format = $genFile->readInt();
                if ($format != (int)0xFFFFFFFE) {
                    throw new Zend_Search_Lucene_Exception('Wrong segments.gen file format');
                }

                $gen1 = $genFile->readLong();
                $gen2 = $genFile->readLong();

                if ($gen1 == $gen2) {
                    return $gen1;
                }

                usleep(self::GENERATION_RETRIEVE_PAUSE * 1000);
            }

            // All passes are failed
            throw new Zend_Search_Lucene_Exception('Index is under processing now');
        } catch (Zend_Search_Lucene_Exception $e) {
            if (strpos($e->getMessage(), 'is not readable') !== false) {
                try {
                    // Try to open old style segments file
                    $segmentsFile = $directory->getFileObject('segments', false);

                    // It's pre-2.1 index
                    return 0;
                } catch (Zend_Search_Lucene_Exception $e) {
                    if (strpos($e->getMessage(), 'is not readable') !== false) {
                        return -1;
                    } else {
                        throw $e;
                    }
                }
            } else {
                throw $e;
            }
        }

        return -1;
    }

    public static function getSegmentFileName($generation)
    {
        if ($generation == 0) {
            return 'segments';
        }

        return 'segments_' . base_convert($generation, 10, 36);
    }

    public function getFormatVersion()
    {
        return $this->_formatVersion;
    }

    public function setFormatVersion($formatVersion)
    {
        if ($formatVersion != self::FORMAT_PRE_2_1  &&
            $formatVersion != self::FORMAT_2_1  &&
            $formatVersion != self::FORMAT_2_3) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Unsupported index format');
        }

        $this->_formatVersion = $formatVersion;
    }

    private function _readPre21SegmentsFile()
    {
        $segmentsFile = $this->_directory->getFileObject('segments');

        $format = $segmentsFile->readInt();

        if ($format != (int)0xFFFFFFFF) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Wrong segments file format');
        }

        // read version
        // $segmentsFile->readLong();
        $segmentsFile->readInt(); $segmentsFile->readInt();

        // read segment name counter
        $segmentsFile->readInt();

        $segments = $segmentsFile->readInt();

        $this->_docCount = 0;

        // read segmentInfos
        for ($count = 0; $count < $segments; $count++) {
            $segName = $segmentsFile->readString();
            $segSize = $segmentsFile->readInt();
            $this->_docCount += $segSize;

            $this->_segmentInfos[$segName] =
                                new Zend_Search_Lucene_Index_SegmentInfo($this->_directory,
                                                                         $segName,
                                                                         $segSize);
        }

        // Use 2.1 as a target version. Index will be reorganized at update time.
        $this->_formatVersion = self::FORMAT_2_1;
    }

    private function _readSegmentsFile()
    {
        $segmentsFile = $this->_directory->getFileObject(self::getSegmentFileName($this->_generation));

        $format = $segmentsFile->readInt();

        if ($format == (int)0xFFFFFFFC) {
            $this->_formatVersion = self::FORMAT_2_3;
        } else if ($format == (int)0xFFFFFFFD) {
            $this->_formatVersion = self::FORMAT_2_1;
        } else {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Unsupported segments file format');
        }

        // read version
        // $segmentsFile->readLong();
        $segmentsFile->readInt(); $segmentsFile->readInt();

        // read segment name counter
        $segmentsFile->readInt();

        $segments = $segmentsFile->readInt();

        $this->_docCount = 0;

        // read segmentInfos
        for ($count = 0; $count < $segments; $count++) {
            $segName = $segmentsFile->readString();
            $segSize = $segmentsFile->readInt();

            // 2.1+ specific properties
            //$delGen          = $segmentsFile->readLong();
            $delGenHigh        = $segmentsFile->readInt();
            $delGenLow         = $segmentsFile->readInt();
            if ($delGenHigh == (int)0xFFFFFFFF  && $delGenLow == (int)0xFFFFFFFF) {
                $delGen = -1; // There are no deletes
            } else {
                $delGen = ($delGenHigh << 32) | $delGenLow;
            }

            if ($this->_formatVersion == self::FORMAT_2_3) {
                $docStoreOffset = $segmentsFile->readInt();

                if ($docStoreOffset != -1) {
                    $docStoreSegment        = $segmentsFile->readString();
                    $docStoreIsCompoundFile = $segmentsFile->readByte();

                    $docStoreOptions = array('offset'     => $docStoreOffset,
                                             'segment'    => $docStoreSegment,
                                             'isCompound' => ($docStoreIsCompoundFile == 1));
                } else {
                    $docStoreOptions = null;
                }
            } else {
                $docStoreOptions = null;
            }

            $hasSingleNormFile = $segmentsFile->readByte();
            $numField          = $segmentsFile->readInt();

            $normGens = array();
            if ($numField != (int)0xFFFFFFFF) {
                for ($count1 = 0; $count1 < $numField; $count1++) {
                    $normGens[] = $segmentsFile->readLong();
                }

                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Separate norm files are not supported. Optimize index to use it with Zend_Search_Lucene.');
            }

            $isCompoundByte     = $segmentsFile->readByte();

            if ($isCompoundByte == 0xFF) {
                // The segment is not a compound file
                $isCompound = false;
            } else if ($isCompoundByte == 0x00) {
                // The status is unknown
                $isCompound = null;
            } else if ($isCompoundByte == 0x01) {
                // The segment is a compound file
                $isCompound = true;
            }

            $this->_docCount += $segSize;

            $this->_segmentInfos[$segName] =
                                new Zend_Search_Lucene_Index_SegmentInfo($this->_directory,
                                                                         $segName,
                                                                         $segSize,
                                                                         $delGen,
                                                                         $docStoreOptions,
                                                                         $hasSingleNormFile,
                                                                         $isCompound);
        }
    }

    public function __construct($directory = null, $create = false)
    {
        if ($directory === null) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Exception('No index directory specified');
        }

        if ($directory instanceof Zend_Search_Lucene_Storage_Directory_Filesystem) {
            $this->_directory      = $directory;
            $this->_closeDirOnExit = false;
        } else {
            $this->_directory      = new Zend_Search_Lucene_Storage_Directory_Filesystem($directory);
            $this->_closeDirOnExit = true;
        }

        $this->_segmentInfos = array();

        // Mark index as "under processing" to prevent other processes from premature index cleaning
        Zend_Search_Lucene_LockManager::obtainReadLock($this->_directory);

        $this->_generation = self::getActualGeneration($this->_directory);

        if ($create) {
            require_once 'Zend/Search/Lucene/Exception.php';
            try {
                Zend_Search_Lucene_LockManager::obtainWriteLock($this->_directory);
            } catch (Zend_Search_Lucene_Exception $e) {
                Zend_Search_Lucene_LockManager::releaseReadLock($this->_directory);

                if (strpos($e->getMessage(), 'Can\'t obtain exclusive index lock') === false) {
                    throw $e;
                } else {
                    throw new Zend_Search_Lucene_Exception('Can\'t create index. It\'s under processing now');
                }
            }

            if ($this->_generation == -1) {
                // Directory doesn't contain existing index, start from 1
                $this->_generation = 1;
                $nameCounter = 0;
            } else {
                // Directory contains existing index
                $segmentsFile = $this->_directory->getFileObject(self::getSegmentFileName($this->_generation));
                $segmentsFile->seek(12); // 12 = 4 (int, file format marker) + 8 (long, index version)

                $nameCounter = $segmentsFile->readInt();
                $this->_generation++;
            }

            Zend_Search_Lucene_Index_Writer::createIndex($this->_directory, $this->_generation, $nameCounter);

            Zend_Search_Lucene_LockManager::releaseWriteLock($this->_directory);
        }

        if ($this->_generation == -1) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Index doesn\'t exists in the specified directory.');
        } else if ($this->_generation == 0) {
            $this->_readPre21SegmentsFile();
        } else {
            $this->_readSegmentsFile();
        }
    }

    private function _close()
    {
        if ($this->_closed) {
            // index is already closed and resources are cleaned up
            return;
        }

        $this->commit();

        // Release "under processing" flag
        Zend_Search_Lucene_LockManager::releaseReadLock($this->_directory);

        if ($this->_closeDirOnExit) {
            $this->_directory->close();
        }

        $this->_directory    = null;
        $this->_writer       = null;
        $this->_segmentInfos = null;

        $this->_closed = true;
    }

    public function addReference()
    {
        $this->_refCount++;
    }

    public function removeReference()
    {
        $this->_refCount--;

        if ($this->_refCount == 0) {
            $this->_close();
        }
    }

    public function __destruct()
    {
        $this->_close();
    }

    private function _getIndexWriter()
    {
        if (!$this->_writer instanceof Zend_Search_Lucene_Index_Writer) {
            $this->_writer = new Zend_Search_Lucene_Index_Writer($this->_directory, $this->_segmentInfos, $this->_formatVersion);
        }

        return $this->_writer;
    }

    public function getDirectory()
    {
        return $this->_directory;
    }

    public function count()
    {
        return $this->_docCount;
    }

    public function maxDoc()
    {
        return $this->count();
    }

    public function numDocs()
    {
        $numDocs = 0;

        foreach ($this->_segmentInfos as $segmentInfo) {
            $numDocs += $segmentInfo->numDocs();
        }

        return $numDocs;
    }

    public function isDeleted($id)
    {
        if ($id >= $this->_docCount) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }

        return $segmentInfo->isDeleted($id - $segmentStartId);
    }

    public static function setDefaultSearchField($fieldName)
    {
        self::$_defaultSearchField = $fieldName;
    }

    public static function getDefaultSearchField()
    {
        return self::$_defaultSearchField;
    }

    public static function setResultSetLimit($limit)
    {
        self::$_resultSetLimit = $limit;
    }

    public static function getResultSetLimit()
    {
        return self::$_resultSetLimit;
    }

    public static function setTermsPerQueryLimit($limit)
    {
        self::$_termsPerQueryLimit = $limit;
    }

    public static function getTermsPerQueryLimit()
    {
        return self::$_termsPerQueryLimit;
    }

    public function getMaxBufferedDocs()
    {
        return $this->_getIndexWriter()->maxBufferedDocs;
    }

    public function setMaxBufferedDocs($maxBufferedDocs)
    {
        $this->_getIndexWriter()->maxBufferedDocs = $maxBufferedDocs;
    }

    public function getMaxMergeDocs()
    {
        return $this->_getIndexWriter()->maxMergeDocs;
    }

    public function setMaxMergeDocs($maxMergeDocs)
    {
        $this->_getIndexWriter()->maxMergeDocs = $maxMergeDocs;
    }

    public function getMergeFactor()
    {
        return $this->_getIndexWriter()->mergeFactor;
    }

    public function setMergeFactor($mergeFactor)
    {
        $this->_getIndexWriter()->mergeFactor = $mergeFactor;
    }

    public function find($query)
    {
        if (is_string($query)) {
            $query = Zend_Search_Lucene_Search_QueryParser::parse($query);
        }

        if (!$query instanceof Zend_Search_Lucene_Search_Query) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Query must be a string or Zend_Search_Lucene_Search_Query object');
        }

        $this->commit();

        $hits   = array();
        $scores = array();
        $ids    = array();

        $query = $query->rewrite($this)->optimize($this);

        $query->execute($this);

        $topScore = 0;

        foreach ($query->matchedDocs() as $id => $num) {
            $docScore = $query->score($id, $this);
            if( $docScore != 0 ) {
                $hit = new Zend_Search_Lucene_Search_QueryHit($this);
                $hit->id = $id;
                $hit->score = $docScore;

                $hits[]   = $hit;
                $ids[]    = $id;
                $scores[] = $docScore;

                if ($docScore > $topScore) {
                    $topScore = $docScore;
                }
            }

            if (self::$_resultSetLimit != 0  &&  count($hits) >= self::$_resultSetLimit) {
                break;
            }
        }

        if (count($hits) == 0) {
            // skip sorting, which may cause a error on empty index
            return array();
        }

        if ($topScore > 1) {
            foreach ($hits as $hit) {
                $hit->score /= $topScore;
            }
        }

        if (func_num_args() == 1) {
            // sort by scores
            array_multisort($scores, SORT_DESC, SORT_NUMERIC,
                            $ids,    SORT_ASC,  SORT_NUMERIC,
                            $hits);
        } else {
            // sort by given field names

            $argList    = func_get_args();
            $fieldNames = $this->getFieldNames();
            $sortArgs   = array();

            require_once 'Zend/Search/Lucene/Exception.php';
            for ($count = 1; $count < count($argList); $count++) {
                $fieldName = $argList[$count];

                if (!is_string($fieldName)) {
                    throw new Zend_Search_Lucene_Exception('Field name must be a string.');
                }

                if (!in_array($fieldName, $fieldNames)) {
                    throw new Zend_Search_Lucene_Exception('Wrong field name.');
                }

                $valuesArray = array();
                foreach ($hits as $hit) {
                    try {
                        $value = $hit->getDocument()->getFieldValue($fieldName);
                    } catch (Zend_Search_Lucene_Exception $e) {
                        if (strpos($e->getMessage(), 'not found') === false) {
                            throw $e;
                        } else {
                            $value = null;
                        }
                    }

                    $valuesArray[] = $value;
                }

                $sortArgs[] = $valuesArray;

                if ($count + 1 < count($argList)  &&  is_integer($argList[$count+1])) {
                    $count++;
                    $sortArgs[] = $argList[$count];

                    if ($count + 1 < count($argList)  &&  is_integer($argList[$count+1])) {
                        $count++;
                        $sortArgs[] = $argList[$count];
                    } else {
                        if ($argList[$count] == SORT_ASC  || $argList[$count] == SORT_DESC) {
                            $sortArgs[] = SORT_REGULAR;
                        } else {
                            $sortArgs[] = SORT_ASC;
                        }
                    }
                } else {
                    $sortArgs[] = SORT_ASC;
                    $sortArgs[] = SORT_REGULAR;
                }
            }

            // Sort by id's if values are equal
            $sortArgs[] = $ids;
            $sortArgs[] = SORT_ASC;
            $sortArgs[] = SORT_NUMERIC;

            // Array to be sorted
            $sortArgs[] = &$hits;

            // Do sort
            call_user_func_array('array_multisort', $sortArgs);
        }

        return $hits;
    }

    public function getFieldNames($indexed = false)
    {
        $result = array();
        foreach( $this->_segmentInfos as $segmentInfo ) {
            $result = array_merge($result, $segmentInfo->getFields($indexed));
        }
        return $result;
    }

    public function getDocument($id)
    {
        if ($id instanceof Zend_Search_Lucene_Search_QueryHit) {

            $id = $id->id;
        }

        if ($id >= $this->_docCount) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }

        $fdxFile = $segmentInfo->openCompoundFile('.fdx');
        $fdxFile->seek(($id-$segmentStartId)*8, SEEK_CUR);
        $fieldValuesPosition = $fdxFile->readLong();

        $fdtFile = $segmentInfo->openCompoundFile('.fdt');
        $fdtFile->seek($fieldValuesPosition, SEEK_CUR);
        $fieldCount = $fdtFile->readVInt();

        $doc = new Zend_Search_Lucene_Document();
        for ($count = 0; $count < $fieldCount; $count++) {
            $fieldNum = $fdtFile->readVInt();
            $bits = $fdtFile->readByte();

            $fieldInfo = $segmentInfo->getField($fieldNum);

            if (!($bits & 2)) { // Text data
                $field = new Zend_Search_Lucene_Field($fieldInfo->name,
                                                      $fdtFile->readString(),
                                                      'UTF-8',
                                                      true,
                                                      $fieldInfo->isIndexed,
                                                      $bits & 1 );
            } else {            // Binary data
                $field = new Zend_Search_Lucene_Field($fieldInfo->name,
                                                      $fdtFile->readBinary(),
                                                      '',
                                                      true,
                                                      $fieldInfo->isIndexed,
                                                      $bits & 1,
                                                      true );
            }

            $doc->addField($field);
        }

        return $doc;
    }

    public function hasTerm(Zend_Search_Lucene_Index_Term $term)
    {
        foreach ($this->_segmentInfos as $segInfo) {
            if ($segInfo->getTermInfo($term) instanceof Zend_Search_Lucene_Index_TermInfo) {
                return true;
            }
        }

        return false;
    }

    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        $subResults = array();
        $segmentStartDocId = 0;

        foreach ($this->_segmentInfos as $segmentInfo) {
            $subResults[] = $segmentInfo->termDocs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        if (count($subResults) == 0) {
            return array();
        } else if (count($subResults) == 0) {
            // Index is optimized (only one segment)
            // Do not perform array reindexing
            return reset($subResults);
        } else {
            $result = call_user_func_array('array_merge', $subResults);
        }

        return $result;
    }

    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        $segmentStartDocId = 0;
        $result = new Zend_Search_Lucene_Index_DocsFilter();

        foreach ($this->_segmentInfos as $segmentInfo) {
            $subResults[] = $segmentInfo->termDocs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        if (count($subResults) == 0) {
            return array();
        } else if (count($subResults) == 0) {
            // Index is optimized (only one segment)
            // Do not perform array reindexing
            return reset($subResults);
        } else {
            $result = call_user_func_array('array_merge', $subResults);
        }

        return $result;
    }

    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        $result = array();
        $segmentStartDocId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            $result += $segmentInfo->termFreqs($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        return $result;
    }

    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        $result = array();
        $segmentStartDocId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            $result += $segmentInfo->termPositions($term, $segmentStartDocId, $docsFilter);

            $segmentStartDocId += $segmentInfo->count();
        }

        return $result;
    }

    public function docFreq(Zend_Search_Lucene_Index_Term $term)
    {
        $result = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            $termInfo = $segInfo->getTermInfo($term);
            if ($termInfo !== null) {
                $result += $termInfo->docFreq;
            }
        }

        return $result;
    }

    public function getSimilarity()
    {
        return Zend_Search_Lucene_Search_Similarity::getDefault();
    }

    public function norm($id, $fieldName)
    {
        if ($id >= $this->_docCount) {
            return null;
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            if ($segmentStartId + $segInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segInfo->count();
        }

        if ($segInfo->isDeleted($id - $segmentStartId)) {
            return 0;
        }

        return $segInfo->norm($id - $segmentStartId, $fieldName);
    }

    public function hasDeletions()
    {
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentInfo->hasDeletions()) {
                return true;
            }
        }

        return false;
    }

    public function delete($id)
    {
        if ($id instanceof Zend_Search_Lucene_Search_QueryHit) {

            $id = $id->id;
        }

        if ($id >= $this->_docCount) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Document id is out of the range.');
        }

        $segmentStartId = 0;
        foreach ($this->_segmentInfos as $segmentInfo) {
            if ($segmentStartId + $segmentInfo->count() > $id) {
                break;
            }

            $segmentStartId += $segmentInfo->count();
        }
        $segmentInfo->delete($id - $segmentStartId);

        $this->_hasChanges = true;
    }

    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        $this->_getIndexWriter()->addDocument($document);
        $this->_docCount++;

        $this->_hasChanges = true;
    }

    private function _updateDocCount()
    {
        $this->_docCount = 0;
        foreach ($this->_segmentInfos as $segInfo) {
            $this->_docCount += $segInfo->count();
        }
    }

    public function commit()
    {
        if ($this->_hasChanges) {
            $this->_getIndexWriter()->commit();

            $this->_updateDocCount();

            $this->_hasChanges = false;
        }
    }

    public function optimize()
    {
        // Commit changes if any changes have been made
        $this->commit();

        if (count($this->_segmentInfos) > 1 || $this->hasDeletions()) {
            $this->_getIndexWriter()->optimize();
            $this->_updateDocCount();
        }
    }

    public function terms()
    {
        $result = array();

        $segmentInfoQueue = new Zend_Search_Lucene_Index_SegmentInfoPriorityQueue();

        foreach ($this->_segmentInfos as $segmentInfo) {
            $segmentInfo->reset();

            // Skip "empty" segments
            if ($segmentInfo->currentTerm() !== null) {
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        while (($segmentInfo = $segmentInfoQueue->pop()) !== null) {
            if ($segmentInfoQueue->top() === null ||
                $segmentInfoQueue->top()->currentTerm()->key() !=
                            $segmentInfo->currentTerm()->key()) {
                // We got new term
                $result[] = $segmentInfo->currentTerm();
            }

            if ($segmentInfo->nextTerm() !== null) {
                // Put segment back into the priority queue
                $segmentInfoQueue->put($segmentInfo);
            }
        }

        return $result;
    }

    private $_termsStreamQueue = null;

    private $_lastTerm = null;

    public function resetTermsStream()
    {
        $this->_termsStreamQueue = new Zend_Search_Lucene_Index_SegmentInfoPriorityQueue();

        foreach ($this->_segmentInfos as $segmentInfo) {
            $segmentInfo->reset();

            // Skip "empty" segments
            if ($segmentInfo->currentTerm() !== null) {
                $this->_termsStreamQueue->put($segmentInfo);
            }
        }

        $this->nextTerm();
    }

    public function skipTo(Zend_Search_Lucene_Index_Term $prefix)
    {
        $segments = array();

        while (($segmentInfo = $this->_termsStreamQueue->pop()) !== null) {
            $segments[] = $segmentInfo;
        }

        foreach ($segments as $segmentInfo) {
            $segmentInfo->skipTo($prefix);

            if ($segmentInfo->currentTerm() !== null) {
                $this->_termsStreamQueue->put($segmentInfo);
            }
        }

        $this->nextTerm();
    }

    public function nextTerm()
    {
        while (($segmentInfo = $this->_termsStreamQueue->pop()) !== null) {
            if ($this->_termsStreamQueue->top() === null ||
                $this->_termsStreamQueue->top()->currentTerm()->key() !=
                            $segmentInfo->currentTerm()->key()) {
                // We got new term
                $this->_lastTerm = $segmentInfo->currentTerm();

                if ($segmentInfo->nextTerm() !== null) {
                    // Put segment back into the priority queue
                    $this->_termsStreamQueue->put($segmentInfo);
                }

                return $this->_lastTerm;
            }

            if ($segmentInfo->nextTerm() !== null) {
                // Put segment back into the priority queue
                $this->_termsStreamQueue->put($segmentInfo);
            }
        }

        // End of stream
        $this->_lastTerm = null;

        return null;
    }

    public function currentTerm()
    {
        return $this->_lastTerm;
    }

    public function closeTermsStream()
    {
        while (($segmentInfo = $this->_termsStreamQueue->pop()) !== null) {
            $segmentInfo->closeTermsStream();
        }

        $this->_termsStreamQueue = null;
        $this->_lastTerm         = null;
    }


    public function undeleteAll()
    {}
}
