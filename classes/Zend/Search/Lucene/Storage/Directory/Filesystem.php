<?php


require_once 'Zend/Search/Lucene/Storage/Directory.php';

require_once 'Zend/Search/Lucene/Storage/File/Filesystem.php';

class Zend_Search_Lucene_Storage_Directory_Filesystem extends Zend_Search_Lucene_Storage_Directory
{

    protected $_dirPath = null;

    protected $_fileHandlers;

    protected static $_defaultFilePermissions = 0666;

    public static function getDefaultFilePermissions()
    {
        return self::$_defaultFilePermissions;
    }

    public static function setDefaultFilePermissions($mode)
    {
        self::$_defaultFilePermissions = $mode;
    }


    public static function mkdirs($dir, $mode = 0777, $recursive = true)
    {
        if (is_null($dir) || $dir === '') {
            return false;
        }
        if (is_dir($dir) || $dir === '/') {
            return true;
        }
        if (self::mkdirs(dirname($dir), $mode, $recursive)) {
            return mkdir($dir, $mode);
        }
        return false;
    }

    public function __construct($path)
    {
        if (!is_dir($path)) {
            if (file_exists($path)) {
                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Path exists, but it\'s not a directory');
            } else {
                if (!self::mkdirs($path)) {
                    require_once 'Zend/Search/Lucene/Exception.php';
                    throw new Zend_Search_Lucene_Exception("Can't create directory '$path'.");
                }
            }
        }
        $this->_dirPath = $path;
        $this->_fileHandlers = array();
    }

    public function close()
    {
        foreach ($this->_fileHandlers as $fileObject) {
            $fileObject->close();
        }

        $this->_fileHandlers = array();
    }

    public function fileList()
    {
        $result = array();

        $dirContent = opendir( $this->_dirPath );
        while (($file = readdir($dirContent)) !== false) {
            if (($file == '..')||($file == '.'))   continue;

            if( !is_dir($this->_dirPath . '/' . $file) ) {
                $result[] = $file;
            }
        }
        closedir($dirContent);

        return $result;
    }

    public function createFile($filename)
    {
        if (isset($this->_fileHandlers[$filename])) {
            $this->_fileHandlers[$filename]->close();
        }
        unset($this->_fileHandlers[$filename]);
        $this->_fileHandlers[$filename] = new Zend_Search_Lucene_Storage_File_Filesystem($this->_dirPath . '/' . $filename, 'w+b');

        // Set file permissions, but don't care about any possible failures, since file may be already
        // created by anther user which has to care about right permissions
        @chmod($this->_dirPath . '/' . $filename, self::$_defaultFilePermissions);

        return $this->_fileHandlers[$filename];
    }

    public function deleteFile($filename)
    {
        if (isset($this->_fileHandlers[$filename])) {
            $this->_fileHandlers[$filename]->close();
        }
        unset($this->_fileHandlers[$filename]);

        global $php_errormsg;
        $trackErrors = ini_get('track_errors'); ini_set('track_errors', '1');
        if (!@unlink($this->_dirPath . '/' . $filename)) {
            ini_set('track_errors', $trackErrors);
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('Can\'t delete file: ' . $php_errormsg);
        }
        ini_set('track_errors', $trackErrors);
    }

    public function purgeFile($filename)
    {
        if (isset($this->_fileHandlers[$filename])) {
            $this->_fileHandlers[$filename]->close();
        }
        unset($this->_fileHandlers[$filename]);
    }

    public function fileExists($filename)
    {
        return isset($this->_fileHandlers[$filename]) ||
               file_exists($this->_dirPath . '/' . $filename);
    }

    public function fileLength($filename)
    {
        if (isset( $this->_fileHandlers[$filename] )) {
            return $this->_fileHandlers[$filename]->size();
        }
        return filesize($this->_dirPath .'/'. $filename);
    }

    public function fileModified($filename)
    {
        return filemtime($this->_dirPath .'/'. $filename);
    }

    public function renameFile($from, $to)
    {
        global $php_errormsg;

        if (isset($this->_fileHandlers[$from])) {
            $this->_fileHandlers[$from]->close();
        }
        unset($this->_fileHandlers[$from]);

        if (isset($this->_fileHandlers[$to])) {
            $this->_fileHandlers[$to]->close();
        }
        unset($this->_fileHandlers[$to]);

        if (file_exists($this->_dirPath . '/' . $to)) {
            if (!unlink($this->_dirPath . '/' . $to)) {
                require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception('Delete operation failed');
            }
        }

        $trackErrors = ini_get('track_errors');
        ini_set('track_errors', '1');

        $success = @rename($this->_dirPath . '/' . $from, $this->_dirPath . '/' . $to);
        if (!$success) {
            ini_set('track_errors', $trackErrors);
            require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception($php_errormsg);
        }

        ini_set('track_errors', $trackErrors);

        return $success;
    }

    public function touchFile($filename)
    {
        return touch($this->_dirPath .'/'. $filename);
    }

    public function getFileObject($filename, $shareHandler = true)
    {
        $fullFilename = $this->_dirPath . '/' . $filename;

        if (!$shareHandler) {
            return new Zend_Search_Lucene_Storage_File_Filesystem($fullFilename);
        }

        if (isset( $this->_fileHandlers[$filename] )) {
            $this->_fileHandlers[$filename]->seek(0);
            return $this->_fileHandlers[$filename];
        }

        $this->_fileHandlers[$filename] = new Zend_Search_Lucene_Storage_File_Filesystem($fullFilename);
        return $this->_fileHandlers[$filename];
    }
}

