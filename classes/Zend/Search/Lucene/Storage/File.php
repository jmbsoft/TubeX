<?php


abstract class Zend_Search_Lucene_Storage_File
{

    abstract protected function _fread($length=1);

    abstract public function seek($offset, $whence=SEEK_SET);

    abstract public function tell();

    abstract public function flush();

    abstract protected function _fwrite($data, $length=null);

    abstract public function lock($lockType, $nonBlockinLock = false);

    abstract public function unlock();

    public function readByte()
    {
        return ord($this->_fread(1));
    }

    public function writeByte($byte)
    {
        return $this->_fwrite(chr($byte), 1);
    }

    public function readBytes($num)
    {
        return $this->_fread($num);
    }

    public function writeBytes($data, $num=null)
    {
        $this->_fwrite($data, $num);
    }

    public function readInt()
    {
        $str = $this->_fread(4);

        return  ord($str[0]) << 24 |
                ord($str[1]) << 16 |
                ord($str[2]) << 8  |
                ord($str[3]);
    }

    public function writeInt($value)
    {
        settype($value, 'integer');
        $this->_fwrite( chr($value>>24 & 0xFF) .
                        chr($value>>16 & 0xFF) .
                        chr($value>>8  & 0xFF) .
                        chr($value     & 0xFF),   4  );
    }

    public function readLong()
    {
        $str = $this->_fread(8);

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

        if (PHP_INT_SIZE > 4) {
            settype($value, 'integer');
            $this->_fwrite( chr($value>>56 & 0xFF) .
                            chr($value>>48 & 0xFF) .
                            chr($value>>40 & 0xFF) .
                            chr($value>>32 & 0xFF) .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF),   8  );
        } else {
            if ($value > 0x7FFFFFFF) {
                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Largest supported segment size (for 32-bit mode) is 2Gb');
            }

            $this->_fwrite( "\x00\x00\x00\x00"     .
                            chr($value>>24 & 0xFF) .
                            chr($value>>16 & 0xFF) .
                            chr($value>>8  & 0xFF) .
                            chr($value     & 0xFF),   8  );
        }
    }

    public function readVInt()
    {
        $nextByte = ord($this->_fread(1));
        $val = $nextByte & 0x7F;

        for ($shift=7; ($nextByte & 0x80) != 0; $shift += 7) {
            $nextByte = ord($this->_fread(1));
            $val |= ($nextByte & 0x7F) << $shift;
        }
        return $val;
    }

    public function writeVInt($value)
    {
        settype($value, 'integer');
        while ($value > 0x7F) {
            $this->_fwrite(chr( ($value & 0x7F)|0x80 ));
            $value >>= 7;
        }
        $this->_fwrite(chr($value));
    }

    public function readString()
    {
        $strlen = $this->readVInt();
        if ($strlen == 0) {
            return '';
        } else {


            $str_val = $this->_fread($strlen);

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
                    $str_val .= $this->_fread($addBytes);
                    $strlen += $addBytes;

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
            $this->_fwrite(str_replace($str, "\x00", "\xC0\x80"));
        } else {
            $this->_fwrite($str);
        }
    }

    public function readBinary()
    {
        return $this->_fread($this->readVInt());
    }
}
