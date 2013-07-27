<?php


require_once 'Zend/Search/Lucene/Storage/File.php';

class Zend_Search_Lucene_Storage_File_Filesystem extends Zend_Search_Lucene_Storage_File
{

    protected $_fileHandle;

    public function __construct($filename, $mode='r+b')
    {
        global $php_errormsg;

        if (strpos($mode, 'w') === false  &&  !is_readable($filename)) {
            // opening for reading non-readable file
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('File \'' . $filename . '\' is not readable.');
        }

        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', '1');

        $this->_fileHandle = @fopen($filename, $mode);

        if ($this->_fileHandle === false) {
            ini_set('track_errors', $trackErrors);
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception($php_errormsg);
        }

        ini_set('track_errors', $trackErrors);
    }

    public function seek($offset, $whence=SEEK_SET)
    {
        return fseek($this->_fileHandle, $offset, $whence);
    }

    public function tell()
    {
        return ftell($this->_fileHandle);
    }

    public function flush()
    {
        return fflush($this->_fileHandle);
    }

    public function close()
    {
        if ($this->_fileHandle !== null ) {
            @fclose($this->_fileHandle);
            $this->_fileHandle = null;
        }
    }

    public function size()
    {
        $position = ftell($this->_fileHandle);
        fseek($this->_fileHandle, 0, SEEK_END);
        $size = ftell($this->_fileHandle);
        fseek($this->_fileHandle,$position);

        return $size;
    }

    protected function _fread($length=1)
    {
        if ($length == 0) {
            return '';
        }

        if ($length < 1024) {
            return fread($this->_fileHandle, $length);
        }

        $data = '';
        while ( $length > 0 && ($nextBlock = fread($this->_fileHandle, $length)) != false ) {
            $data .= $nextBlock;
            $length -= strlen($nextBlock);
        }
        return $data;
    }

    protected function _fwrite($data, $length=null)
    {
        if ($length === null ) {
            fwrite($this->_fileHandle, $data);
        } else {
            fwrite($this->_fileHandle, $data, $length);
        }
    }

    public function lock($lockType, $nonBlockingLock = false)
    {
        if ($nonBlockingLock) {
            return flock($this->_fileHandle, $lockType | LOCK_NB);
        } else {
            return flock($this->_fileHandle, $lockType);
        }
    }

    public function unlock()
    {
        if ($this->_fileHandle !== null ) {
            return flock($this->_fileHandle, LOCK_UN);
        } else {
            return true;
        }
    }
}

