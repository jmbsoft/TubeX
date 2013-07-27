<?php


require_once 'Zend/Search/Lucene/Index/SegmentInfo.php';

require_once 'Zend/Search/Lucene/Index/SegmentWriter.php';

class Zend_Search_Lucene_Index_SegmentWriter_StreamWriter extends Zend_Search_Lucene_Index_SegmentWriter
{

    public function __construct(Zend_Search_Lucene_Storage_Directory $directory, $name)
    {
        parent::__construct($directory, $name);
    }

    public function createStoredFieldsFiles()
    {
        $this->_fdxFile = $this->_directory->createFile($this->_name . '.fdx');
        $this->_fdtFile = $this->_directory->createFile($this->_name . '.fdt');

        $this->_files[] = $this->_name . '.fdx';
        $this->_files[] = $this->_name . '.fdt';
    }

    public function addNorm($fieldName, $normVector)
    {
        if (isset($this->_norms[$fieldName])) {
            $this->_norms[$fieldName] .= $normVector;
        } else {
            $this->_norms[$fieldName] = $normVector;
        }
    }

    public function close()
    {
        if ($this->_docCount == 0) {
            return null;
        }

        $this->_dumpFNM();
        $this->_generateCFS();

        return new Zend_Search_Lucene_Index_SegmentInfo($this->_directory,
                                                        $this->_name,
                                                        $this->_docCount,
                                                        -1,
                                                        null,
                                                        true,
                                                        true);
    }
}

