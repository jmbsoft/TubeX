<?php


require_once 'Zend/Search/Lucene/Storage/File.php';

class Zend_Search_Lucene_Storage_File_Memory extends Zend_Search_Lucene_Storage_File
{

    private $_data;

    private $_position = 0;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    protected function _fread($length = 1)
    {
        $returnValue = substr($this->_data, $this->_position, $length);
        $this->_position += $length;
        return $returnValue;
    }

    public function seek($offset, $whence=SEEK_SET)
    {
        switch ($whence) {
            case SEEK_SET:
                $this->_position = $offset;
                break;

            case SEEK_CUR:
                $this->_position += $offset;
                break;

            case SEEK_END:
                $this->_position = strlen($this->_data);
                $this->_position += $offset;
                break;

            default:
                break;
        }
    }

    public function tell()
    {
        return $this->_position;
    }

    public function flush()
    {
        // Do nothing

        return true;
    }

    protected function _fwrite($data, $length=null)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        if ($length !== null) {
            $this->_data .= substr($data, 0, $length);
        } else {
            $this->_data .= $data;
        }

        $this->_position = strlen($this->_data);
    }

    public function lock($lockType, $nonBlockinLock = false)
    {
        // Memory files can't be shared
        // do nothing

        return true;
    }

    public function unlock()
    {
        // Memory files can't be shared
        // do nothing
    }

    public function readByte()
    {
        return ord($this->_data[$this->_position++]);
    }

    public function writeByte($byte)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        $this->_data .= chr($byte);
        $this->_position = strlen($this->_data);

        return 1;
    }

    public function readBytes($num)
    {
        $returnValue = substr($this->_data, $this->_position, $num);
        $this->_position += $num;

        return $returnValue;
    }

    public function writeBytes($data, $num=null)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        if ($num !== null) {
            $this->_data .= substr($data, 0, $num);
        } else {
            $this->_data .= $data;
        }

        $this->_position = strlen($this->_data);
    }

    public function readInt()
    {
        $str = substr($this->_data, $this->_position, 4);
        $this->_position += 4;

        return  ord($str[0]) << 24 |
                ord($str[1]) << 16 |
                ord($str[2]) << 8  |
                ord($str[3]);
    }

    public function writeInt($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        settype($value, 'integer');
        $this->_data .= chr($value>>24 & 0xFF) .
                        chr($value>>16 & 0xFF) .
                        chr($value>>8  & 0xFF) .
                        chr($value     & 0xFF);

        $this->_position = strlen($this->_data);
    }

    public function readLong()
    {
        $str = substr($this->_data, $this->_position, 8);
        $this->_position += 8;

        if (PHP_INT_SIZE > 4) {
            return  ord($str[0]) << 56  |
                    ord($str[1]) << 48  |
                    ord($str[2]) << 40  |
                    ord($str[3]) << 32  |
                    ord($str[4]) << 24  |
                    ord($str[5]) << 16  |
                    ord($str[6]) << 8   |
                    ord($str[7]);
        } else {
            if ((ord($str[0])          != 0) ||
                (ord($str[1])          != 0) ||
                (ord($str[2])          != 0) ||
                (ord($str[3])          != 0) ||
                ((ord($str[0]) & 0x80) != 0)) {
                    require_once 'Zend/Search/Lucene/Exception.php';
                    throw new Zend_Search_Lucene_Exception('Largest supported segment size (for 32-bit mode) is 2Gb');
            }

            return  ord($str[4]) << 24  |
                    ord($str[5]) << 16  |
                    ord($str[6]) << 8   |
                    ord($str[7]);
        }
    }

    public function writeLong($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        if (PHP_INT_SIZE > 4) {
            settype($value, 'integer');
            $this->_data .= chr($value>>56 & 0xFF) .
                            chr($value>>48 & 0xFF) .
                            chr($value>>40 & 0xFF) .
                            chr($value>>32 & 0xFF) .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF);
        } else {
            if ($value > 0x7FFFFFFF) {
                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Largest supported segment size (for 32-bit mode) is 2Gb');
            }

            $this->_data .= chr(0) . chr(0) . chr(0) . chr(0) .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF);
        }

        $this->_position = strlen($this->_data);
    }

    public function readVInt()
    {
        $nextByte = ord($this->_data[$this->_position++]);
        $val = $nextByte & 0x7F;

        for ($shift=7; ($nextByte & 0x80) != 0; $shift += 7) {
            $nextByte = ord($this->_data[$this->_position++]);
            $val |= ($nextByte & 0x7F) << $shift;
        }
        return $val;
    }

    public function writeVInt($value)
    {
        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        settype($value, 'integer');
        while ($value > 0x7F) {
            $this->_data .= chr( ($value & 0x7F)|0x80 );
            $value >>= 7;
        }
        $this->_data .= chr($value);

        $this->_position = strlen($this->_data);
    }

    public function readString()
    {
        $strlen = $this->readVInt();
        if ($strlen == 0) {
            return '';
        } else {


            $str_val = substr($this->_data, $this->_position, $strlen);
            $this->_position += $strlen;

            for ($count = 0; $count < $strlen; $count++ ) {
                if (( ord($str_val[$count]) & 0xC0 ) == 0xC0) {
                    $addBytes = 1;
                    if (ord($str_val[$count]) & 0x20 ) {
                        $addBytes++;

                        // Never used. Java2 doesn't encode strings in four bytes
                        if (ord($str_val[$count]) & 0x10 ) {
                            $addBytes++;
                        }
                    }
                    $str_val .= substr($this->_data, $this->_position, $addBytes);
                    $this->_position += $addBytes;
                    $strlen          += $addBytes;

                    // Check for null character. Java2 encodes null character
                    // in two bytes.
                    if (ord($str_val[$count])   == 0xC0 &&
                        ord($str_val[$count+1]) == 0x80   ) {
                        $str_val[$count] = 0;
                        $str_val = substr($str_val,0,$count+1)
                                 . substr($str_val,$count+2);
                    }
                    $count += $addBytes;
                }
            }

            return $str_val;
        }
    }

    public function writeString($str)
    {


        // We do not need to check if file position points to the end of "file".
        // Only append operation is supported now

        // convert input to a string before iterating string characters
        settype($str, 'string');

        $chars = $strlen = strlen($str);
        $containNullChars = false;

        for ($count = 0; $count < $strlen; $count++ ) {

            if ((ord($str[$count]) & 0xC0) == 0xC0) {
                $addBytes = 1;
                if (ord($str[$count]) & 0x20 ) {
                    $addBytes++;

                    // Never used. Java2 doesn't encode strings in four bytes
                    // and we dont't support non-BMP characters
                    if (ord($str[$count]) & 0x10 ) {
                        $addBytes++;
                    }
                }
                $chars -= $addBytes;

                if (ord($str[$count]) == 0 ) {
                    $containNullChars = true;
                }
                $count += $addBytes;
            }
        }

        if ($chars < 0) {
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Invalid UTF-8 string');
        }

        $this->writeVInt($chars);
        if ($containNullChars) {
            $this->_data .= str_replace($str, "\x00", "\xC0\x80");

        } else {
            $this->_data .= $str;
        }

        $this->_position = strlen($this->_data);
    }

    public function readBinary()
    {
        $length = $this->readVInt();
        $returnValue = substr($this->_data, $this->_position, $length);
        $this->_position += $length;
        return $returnValue;
    }
}

