<?php


require_once 'Zend/Gdata/App/BaseMediaSource.php';

class Zend_Gdata_App_MediaFileSource extends Zend_Gdata_App_BaseMediaSource
{

    protected $_filename = null;

    protected $_contentType = null;

    public function __construct($filename)
    {
        $this->setFilename($filename);
    }

    public function encode()
    {
        if ($this->getFilename() !== null && 
            is_readable($this->getFilename())) {

            // Retrieves the file, using the include path
            $fileHandle = fopen($this->getFilename(), 'r', true);
            $result = fread($fileHandle, filesize($this->getFilename()));
            if ($result === false) {
                require_once 'Zend/Gdata/App/IOException.php';
                throw new Zend_Gdata_App_IOException("Error reading file - " .
                        $this->getFilename() . '. Read failed.');
            }
            fclose($fileHandle);
            return $result;
        } else {
            require_once 'Zend/Gdata/App/IOException.php';
            throw new Zend_Gdata_App_IOException("Error reading file - " . 
                    $this->getFilename() . '. File is not readable.');
        }
    }

    public function getFilename()
    {
        return $this->_filename;
    }

    public function setFilename($value)
    {
        $this->_filename = $value;
        return $this;
    }

    public function getContentType()
    {
        return $this->_contentType;
    }

    public function setContentType($value)
    {
        $this->_contentType = $value;
        return $this;
    }

    public function __toString()
    {
        return $this->getFilename();
    }
    
}
