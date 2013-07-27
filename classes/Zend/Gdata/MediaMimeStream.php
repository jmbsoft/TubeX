<?php


class Zend_Gdata_MediaMimeStream
{

    // TODO (jhartmann) Add support for charset [ZF-5768]
    const XML_HEADER = "Content-Type: application/atom+xml\r\n\r\n";

    const PART_XML_STRING = 0;

    const PART_FILE_BINARY = 1;

    const PART_CLOSING_XML_STRING = 2;

    const MAX_BUFFER_SIZE = 8192;

    protected $_boundaryLine = null;

    protected $_boundaryString = null;

    protected $_closingBoundaryLine = null;

    protected $_fileHandle = null;

    protected $_fileHeaders = null;

    protected $_fileContentType = null;

    protected $_fileSize = null;

    protected $_totalSize = null;

    protected $_xmlString = null;

    protected $_bytesRead = 0;

    protected $_currentPart = 0;

    protected $_parts = null;

    protected $_doneReading = false;

    public function __construct($xmlString = null, $filePath = null,
        $fileContentType = null)
    {
        $this->_xmlString = $xmlString;
        $this->_filePath = $filePath;
        $this->_fileContentType = $fileContentType;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            require_once 'Zend/Gdata/App/IOException.php';
            throw new Zend_Gdata_App_IOException('File to be uploaded at ' .
                $filePath . ' does not exist or is not readable.');
        }

        $this->_fileHandle = fopen($filePath, 'rb', true);
        $this->generateBoundaries();
        $this->calculatePartSizes();
    }

    private function generateBoundaries()
    {
        $this->_boundaryString = '=_' . md5(microtime(1) . rand(1,20));
        $this->_boundaryLine = "\r\n" . '--' . $this->_boundaryString . "\r\n";
        $this->_closingBoundaryLine = "\r\n" . '--' . $this->_boundaryString .
            '--';
    }

    private function calculatePartSizes()
    {
        $this->_fileHeaders = 'Content-Type: ' . $this->_fileContentType .
            "\r\n" . 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
        $this->_fileSize = filesize($this->_filePath);

        $stringSection = $this->_boundaryLine . self::XML_HEADER .
            $this->_xmlString . "\r\n" . $this->_boundaryLine .
            $this->_fileHeaders;
        $stringLen = strlen($stringSection);
        $closingBoundaryLen = strlen($this->_closingBoundaryLine);

        $this->_parts = array();
        $this->_parts[] = array($stringLen, $stringSection);
        $this->_parts[] = array($this->_fileSize);
        $this->_parts[] = array($closingBoundaryLen,
            $this->_closingBoundaryLine);

        $this->_totalSize = $stringLen + $this->_fileSize + $closingBoundaryLen;
    }

    private function smartfread($length)
    {
        if ($length < 1) {
            return '';
        } else {
            return fread($this->_fileHandle, $length);
        }
    }

    private function strlen2($string)
    {
        return array_sum(char_count($string));
    }

    public function read($bufferSize)
    {
        if ($bufferSize > self::MAX_BUFFER_SIZE) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException('Buffer size ' .
                'is larger than the supported max of ' . self::MAX_BUFFER_SIZE);
        }

        // handle edge cases where bytesRead is negative
        if ($this->_bytesRead < 0) {
            $this->_bytesRead = 0;
        }

        $returnString = null;
        // If entire message is smaller than the buffer, just return everything
        if ($bufferSize > $this->_totalSize) {
            $returnString = $this->_parts[self::PART_XML_STRING][1];
            $returnString .= fread($this->_fileHandle, $bufferSize);
            $returnString .= $this->_closingBoundaryLine;
            $this->closeFileHandle();
            $this->_doneReading = true;
            return $returnString;
        }

        // increment internal counters
        $readTo = $this->_bytesRead + $bufferSize;
        $sizeOfCurrentPart = $this->_parts[$this->_currentPart][0];
        $sizeOfNextPart = 0;

        // if we are in a part past the current part, exit
        if ($this->_currentPart > self::PART_CLOSING_XML_STRING) {
            $this->_doneReading = true;
            return;
        }

        // if bytes read is bigger than the current part and we are
        // at the end, return
        if (($this->_bytesRead > $sizeOfCurrentPart) &&
            ($this->_currentPart == self::PART_CLOSING_XML_STRING)) {
                $this->_doneReading = true;
                return;
        }

        // check if we have a next part
        if ($this->_currentPart != self::PART_CLOSING_XML_STRING) {
            $nextPart = $this->_currentPart + 1;
            $sizeOfNextPart = $this->_parts[$nextPart][0];
        }

        $readIntoNextPart = false;
        $readFromRemainingPart = null;
        $readFromNextPart = null;

        // are we crossing into multiple sections of the message in
        // this read?
        if ($readTo > ($sizeOfCurrentPart + $sizeOfNextPart)) {
            if ($this->_currentPart == self::PART_XML_STRING) {
                // If we are in XML string and have crossed over the file
                // return that and whatever we can from the closing boundary
                // string.
                $returnString = $this->_parts[self::PART_XML_STRING][1];
                unset($this->_parts[self::PART_XML_STRING]);
                $returnString .= fread($this->_fileHandle,
                    self::MAX_BUFFER_SIZE);
                $this->closeFileHandle();

                $readFromClosingString = $readTo -
                    ($sizeOfCurrentPart + $sizeOfNextPart);
                $returnString .= substr(
                    $this->_parts[self::PART_CLOSING_XML_STRING][1], 0,
                    $readFromClosingString);
                $this->_bytesRead = $readFromClosingString;
                $this->_currentPart = self::PART_CLOSING_XML_STRING;
                return $returnString;

            } elseif ($this->_currentPart == self::PART_FILE_BINARY) {
                // We have read past the entire message, so return it.
                $returnString .= fread($this->_fileHandle,
                    self::MAX_BUFFER_SIZE);
                $returnString .= $this->_closingBoundaryLine;
                $this->closeFileHandle();
                $this->_doneReading = true;
                return $returnString;
            }
        // are we just crossing from one section into another?
        } elseif ($readTo >= $sizeOfCurrentPart) {
            $readIntoNextPart = true;
            $readFromRemainingPart = $sizeOfCurrentPart - $this->_bytesRead;
            $readFromNextPart = $readTo - $sizeOfCurrentPart;
        }

        if (!$readIntoNextPart) {
            // we are not crossing any section so just return something
            // from the current part
            switch ($this->_currentPart) {
                case self::PART_XML_STRING:
                    $returnString = $this->readFromStringPart(
                        $this->_currentPart, $this->_bytesRead, $bufferSize);
                    break;
                case self::PART_FILE_BINARY:
                    $returnString = fread($this->_fileHandle, $bufferSize);
                    break;
                case self::PART_CLOSING_XML_STRING:
                    $returnString = $this->readFromStringPart(
                        $this->_currentPart, $this->_bytesRead, $bufferSize);
                    break;
            }
        } else {
            // we are crossing from one section to another, so figure out
            // where we are coming from and going to
            switch ($this->_currentPart) {
                case self::PART_XML_STRING:
                    // crossing from string to file
                    $returnString = $this->readFromStringPart(
                        $this->_currentPart, $this->_bytesRead,
                        $readFromRemainingPart);
                    // free up string
                    unset($this->_parts[self::PART_XML_STRING]);
                    $returnString .= $this->smartfread($this->_fileHandle,
                            $readFromNextPart);
                    $this->_bytesRead = $readFromNextPart - 1;
                    break;
                case self::PART_FILE_BINARY:
                    // skipping past file section
                    $returnString = $this->smartfread($this->_fileHandle,
                            $readFromRemainingPart);
                    $this->closeFileHandle();
                    // read closing boundary string
                    $returnString = $this->readFromStringPart(
                        self::PART_CLOSING_XML_STRING, 0, $readFromNextPart);
                    // have we read past the entire closing boundary string?
                    if ($readFromNextPart >=
                        $this->_parts[self::PART_CLOSING_XML_STRING][0]) {
                        $this->_doneReading = true;
                        return $returnString;
                    }

                    // Reset counter appropriately since we are now just
                    // counting how much of the final string is being read.
                    $this->_bytesRead = $readFromNextPart - 1;
                    break;
                case self::PART_CLOSING_XML_STRING:
                    // reading past the end of the closing boundary
                    if ($readFromRemainingPart > 0) {
                        $returnString = $this->readFromStringPart(
                            $this->_currentPart, $this->_bytesRead,
                            $readFromRemainingPart);
                        $this->_doneReading = true;
                    }
                    return $returnString;
            }
            $this->_currentPart++;
        }
        $this->_bytesRead += $bufferSize;
        return $returnString;
    }

    private function readFromStringPart($part, $start, $length)
    {
        return substr($this->_parts[$part][1], $start, $length);
    }

    public function getTotalSize()
    {
        return $this->_totalSize;
    }

    public function hasData()
    {
        return !($this->_doneReading);
    }

    protected function closeFileHandle()
    {
        if ($this->_fileHandle !== null) {
            fclose($this->_fileHandle);
        }
    }

    public function getContentType()
    {
        return 'multipart/related; boundary="' .
            $this->_boundaryString . '"' . "\r\n";
    }

}
