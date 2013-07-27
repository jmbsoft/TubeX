<?php


require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

abstract class Zend_Search_Lucene_Index_SegmentWriter
{

    public static $indexInterval = 128;

    public static $skipInterval = 0x7FFFFFFF;

    public static $maxSkipLevels = 0;

    protected $_docCount = 0;

    protected $_name;

    protected $_directory;

    protected $_files = array();

    protected $_fields = array();

    protected $_norms = array();

    protected $_fdxFile = null;

    protected $_fdtFile = null;

    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, $name)
    {
        $this->_directory = $directory;
        $this->_name      = $name;
    }

    public function addField(Zend_Search_Lucene_Field $field)
    {
        if (!isset($this->_fields[$field->name])) {
            $fieldNumber = count($this->_fields);
            $this->_fields[$field->name] =
                                new Zend_Search_Lucene_Index_FieldInfo($field->name,
                                                                       $field->isIndexed,
                                                                       $fieldNumber,
                                                                       $field->storeTermVector);

            return $fieldNumber;
        } else {
            $this->_fields[$field->name]->isIndexed       |= $field->isIndexed;
            $this->_fields[$field->name]->storeTermVector |= $field->storeTermVector;

            return $this->_fields[$field->name]->number;
        }
    }

    public function addFieldInfo(Zend_Search_Lucene_Index_FieldInfo $fieldInfo)
    {
        if (!isset($this->_fields[$fieldInfo->name])) {
            $fieldNumber = count($this->_fields);
            $this->_fields[$fieldInfo->name] =
                                new Zend_Search_Lucene_Index_FieldInfo($fieldInfo->name,
                                                                       $fieldInfo->isIndexed,
                                                                       $fieldNumber,
                                                                       $fieldInfo->storeTermVector);

            return $fieldNumber;
        } else {
            $this->_fields[$fieldInfo->name]->isIndexed       |= $fieldInfo->isIndexed;
            $this->_fields[$fieldInfo->name]->storeTermVector |= $fieldInfo->storeTermVector;

            return $this->_fields[$fieldInfo->name]->number;
        }
    }

    public function getFieldInfos()
    {
        return $this->_fields;
    }

    public function addStoredFields($storedFields)
    {
        if (!isset($this->_fdxFile)) {
            $this->_fdxFile = $this->_directory->createFile($this->_name . '.fdx');
            $this->_fdtFile = $this->_directory->createFile($this->_name . '.fdt');

            $this->_files[] = $this->_name . '.fdx';
            $this->_files[] = $this->_name . '.fdt';
        }

        $this->_fdxFile->writeLong($this->_fdtFile->tell());
        $this->_fdtFile->writeVInt(count($storedFields));
        foreach ($storedFields as $field) {
            $this->_fdtFile->writeVInt($this->_fields[$field->name]->number);
            $fieldBits = ($field->isTokenized ? 0x01 : 0x00) |
                         ($field->isBinary ?    0x02 : 0x00) |
                         0x00; /* 0x04 - third bit, compressed (ZLIB) */
            $this->_fdtFile->writeByte($fieldBits);
            if ($field->isBinary) {
                $this->_fdtFile->writeVInt(strlen($field->value));
                $this->_fdtFile->writeBytes($field->value);
            } else {
                $this->_fdtFile->writeString($field->getUtf8Value());
            }
        }

        $this->_docCount++;
    }

    public function count()
    {
        return $this->_docCount;
    }

    public function getName()
    {
        return $this->_name;
    }

    protected function _dumpFNM()
    {
        $fnmFile = $this->_directory->createFile($this->_name . '.fnm');
        $fnmFile->writeVInt(count($this->_fields));

        $nrmFile = $this->_directory->createFile($this->_name . '.nrm');
        // Write header
        $nrmFile->writeBytes('NRM');
        // Write format specifier
        $nrmFile->writeByte((int)0xFF);

        foreach ($this->_fields as $field) {
            $fnmFile->writeString($field->name);
            $fnmFile->writeByte(($field->isIndexed       ? 0x01 : 0x00) |
                                ($field->storeTermVector ? 0x02 : 0x00)
// not supported yet            0x04 /* term positions are stored with the term vectors */ |
// not supported yet            0x08 /* term offsets are stored with the term vectors */   |
                               );

            if ($field->isIndexed) {
                // pre-2.1 index mode (not used now)
                // $normFileName = $this->_name . '.f' . $field->number;
                // $fFile = $this->_directory->createFile($normFileName);
                // $fFile->writeBytes($this->_norms[$field->name]);
                // $this->_files[] = $normFileName;

                $nrmFile->writeBytes($this->_norms[$field->name]);
            }
        }

        $this->_files[] = $this->_name . '.fnm';
        $this->_files[] = $this->_name . '.nrm';
    }

    private $_tisFile = null;

    private $_tiiFile = null;

    private $_frqFile = null;

    private $_prxFile = null;

    private $_termCount;

    private $_prevTerm;

    private $_prevTermInfo;

    private $_prevIndexTerm;

    private $_prevIndexTermInfo;

    private $_lastIndexPosition;

    public function initializeDictionaryFiles()
    {
        $this->_tisFile = $this->_directory->createFile($this->_name . '.tis');
        $this->_tisFile->writeInt((int)0xFFFFFFFD);
        $this->_tisFile->writeLong(0 /* dummy data for terms count */);
        $this->_tisFile->writeInt(self::$indexInterval);
        $this->_tisFile->writeInt(self::$skipInterval);
        $this->_tisFile->writeInt(self::$maxSkipLevels);

        $this->_tiiFile = $this->_directory->createFile($this->_name . '.tii');
        $this->_tiiFile->writeInt((int)0xFFFFFFFD);
        $this->_tiiFile->writeLong(0 /* dummy data for terms count */);
        $this->_tiiFile->writeInt(self::$indexInterval);
        $this->_tiiFile->writeInt(self::$skipInterval);
        $this->_tiiFile->writeInt(self::$maxSkipLevels);

        $this->_tiiFile->writeVInt(0);                    // preffix length
        $this->_tiiFile->writeString('');                 // suffix
        $this->_tiiFile->writeInt((int)0xFFFFFFFF);       // field number
        $this->_tiiFile->writeByte((int)0x0F);
        $this->_tiiFile->writeVInt(0);                    // DocFreq
        $this->_tiiFile->writeVInt(0);                    // FreqDelta
        $this->_tiiFile->writeVInt(0);                    // ProxDelta
        $this->_tiiFile->writeVInt(24);                   // IndexDelta

        $this->_frqFile = $this->_directory->createFile($this->_name . '.frq');
        $this->_prxFile = $this->_directory->createFile($this->_name . '.prx');

        $this->_files[] = $this->_name . '.tis';
        $this->_files[] = $this->_name . '.tii';
        $this->_files[] = $this->_name . '.frq';
        $this->_files[] = $this->_name . '.prx';

        $this->_prevTerm          = null;
        $this->_prevTermInfo      = null;
        $this->_prevIndexTerm     = null;
        $this->_prevIndexTermInfo = null;
        $this->_lastIndexPosition = 24;
        $this->_termCount         = 0;

    }

    public function addTerm($termEntry, $termDocs)
    {
        $freqPointer = $this->_frqFile->tell();
        $proxPointer = $this->_prxFile->tell();

        $prevDoc = 0;
        foreach ($termDocs as $docId => $termPositions) {
            $docDelta = ($docId - $prevDoc)*2;
            $prevDoc = $docId;
            if (count($termPositions) > 1) {
                $this->_frqFile->writeVInt($docDelta);
                $this->_frqFile->writeVInt(count($termPositions));
            } else {
                $this->_frqFile->writeVInt($docDelta + 1);
            }

            $prevPosition = 0;
            foreach ($termPositions as $position) {
                $this->_prxFile->writeVInt($position - $prevPosition);
                $prevPosition = $position;
            }
        }

        if (count($termDocs) >= self::$skipInterval) {

            $skipOffset = $this->_frqFile->tell() - $freqPointer;
        } else {
            $skipOffset = 0;
        }

        $term = new Zend_Search_Lucene_Index_Term($termEntry->text,
                                                  $this->_fields[$termEntry->field]->number);
        $termInfo = new Zend_Search_Lucene_Index_TermInfo(count($termDocs),
                                                          $freqPointer, $proxPointer, $skipOffset);

        $this->_dumpTermDictEntry($this->_tisFile, $this->_prevTerm, $term, $this->_prevTermInfo, $termInfo);

        if (($this->_termCount + 1) % self::$indexInterval == 0) {
            $this->_dumpTermDictEntry($this->_tiiFile, $this->_prevIndexTerm, $term, $this->_prevIndexTermInfo, $termInfo);

            $indexPosition = $this->_tisFile->tell();
            $this->_tiiFile->writeVInt($indexPosition - $this->_lastIndexPosition);
            $this->_lastIndexPosition = $indexPosition;

        }
        $this->_termCount++;
    }

    public function closeDictionaryFiles()
    {
        $this->_tisFile->seek(4);
        $this->_tisFile->writeLong($this->_termCount);

        $this->_tiiFile->seek(4);
        // + 1 is used to count an additional special index entry (empty term at the start of the list)
        $this->_tiiFile->writeLong(($this->_termCount - $this->_termCount % self::$indexInterval)/self::$indexInterval + 1);
    }

    protected function _dumpTermDictEntry(Zend_Search_Lucene_Storage_File $dicFile,
                                        &$prevTerm,     Zend_Search_Lucene_Index_Term     $term,
                                        &$prevTermInfo, Zend_Search_Lucene_Index_TermInfo $termInfo)
    {
        if (isset($prevTerm) && $prevTerm->field == $term->field) {
            $matchedBytes = 0;
            $maxBytes = min(strlen($prevTerm->text), strlen($term->text));
            while ($matchedBytes < $maxBytes  &&
                   $prevTerm->text[$matchedBytes] == $term->text[$matchedBytes]) {
                $matchedBytes++;
            }

            // Calculate actual matched UTF-8 pattern
            $prefixBytes = 0;
            $prefixChars = 0;
            while ($prefixBytes < $matchedBytes) {
                $charBytes = 1;
                if ((ord($term->text[$prefixBytes]) & 0xC0) == 0xC0) {
                    $charBytes++;
                    if (ord($term->text[$prefixBytes]) & 0x20 ) {
                        $charBytes++;
                        if (ord($term->text[$prefixBytes]) & 0x10 ) {
                            $charBytes++;
                        }
                    }
                }

                if ($prefixBytes + $charBytes > $matchedBytes) {
                    // char crosses matched bytes boundary
                    // skip char
                    break;
                }

                $prefixChars++;
                $prefixBytes += $charBytes;
            }

            // Write preffix length
            $dicFile->writeVInt($prefixChars);
            // Write suffix
            $dicFile->writeString(substr($term->text, $prefixBytes));
        } else {
            // Write preffix length
            $dicFile->writeVInt(0);
            // Write suffix
            $dicFile->writeString($term->text);
        }
        // Write field number
        $dicFile->writeVInt($term->field);
        // DocFreq (the count of documents which contain the term)
        $dicFile->writeVInt($termInfo->docFreq);

        $prevTerm = $term;

        if (!isset($prevTermInfo)) {
            // Write FreqDelta
            $dicFile->writeVInt($termInfo->freqPointer);
            // Write ProxDelta
            $dicFile->writeVInt($termInfo->proxPointer);
        } else {
            // Write FreqDelta
            $dicFile->writeVInt($termInfo->freqPointer - $prevTermInfo->freqPointer);
            // Write ProxDelta
            $dicFile->writeVInt($termInfo->proxPointer - $prevTermInfo->proxPointer);
        }
        // Write SkipOffset - it's not 0 when $termInfo->docFreq > self::$skipInterval
        if ($termInfo->skipOffset != 0) {
            $dicFile->writeVInt($termInfo->skipOffset);
        }

        $prevTermInfo = $termInfo;
    }

    protected function _generateCFS()
    {
        $cfsFile = $this->_directory->createFile($this->_name . '.cfs');
        $cfsFile->writeVInt(count($this->_files));

        $dataOffsetPointers = array();
        foreach ($this->_files as $fileName) {
            $dataOffsetPointers[$fileName] = $cfsFile->tell();
            $cfsFile->writeLong(0); // write dummy data
            $cfsFile->writeString($fileName);
        }

        foreach ($this->_files as $fileName) {
            // Get actual data offset
            $dataOffset = $cfsFile->tell();
            // Seek to the data offset pointer
            $cfsFile->seek($dataOffsetPointers[$fileName]);
            // Write actual data offset value
            $cfsFile->writeLong($dataOffset);
            // Seek back to the end of file
            $cfsFile->seek($dataOffset);

            $dataFile = $this->_directory->getFileObject($fileName);

            $byteCount = $this->_directory->fileLength($fileName);
            while ($byteCount > 0) {
                $data = $dataFile->readBytes(min($byteCount, 131072 /*128Kb*/));
                $byteCount -= strlen($data);
                $cfsFile->writeBytes($data);
            }

            $this->_directory->deleteFile($fileName);
        }
    }

    abstract public function close();
}

