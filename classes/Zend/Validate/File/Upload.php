<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Upload extends Zend_Validate_Abstract
{

    const INI_SIZE       = 'fileUploadErrorIniSize';
    const FORM_SIZE      = 'fileUploadErrorFormSize';
    const PARTIAL        = 'fileUploadErrorPartial';
    const NO_FILE        = 'fileUploadErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadErrorCantWrite';
    const EXTENSION      = 'fileUploadErrorExtension';
    const ATTACK         = 'fileUploadErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadErrorFileNotFound';
    const UNKNOWN        = 'fileUploadErrorUnknown';


    protected $_messageTemplates = array(
        self::INI_SIZE       => "The file '%value%' exceeds the defined ini size",
        self::FORM_SIZE      => "The file '%value%' exceeds the defined form size",
        self::PARTIAL        => "The file '%value%' was only partially uploaded",
        self::NO_FILE        => "The file '%value%' was not uploaded",
        self::NO_TMP_DIR     => "No temporary directory was found for the file '%value%'",
        self::CANT_WRITE     => "The file '%value%' can't be written",
        self::EXTENSION      => "The extension returned an error while uploading the file '%value%'",
        self::ATTACK         => "The file '%value%' was illegal uploaded, possible attack",
        self::FILE_NOT_FOUND => "The file '%value%' was not found",
        self::UNKNOWN        => "Unknown error while uploading the file '%value%'"
    );

    protected $_files = array();

    public function __construct($files = array())
    {
        $this->setFiles($files);
    }

    public function getFiles($file = null)
    {
        if ($file !== null) {
            $return = array();
            foreach ($this->_files as $name => $content) {
                if ($name === $file) {
                    $return[$file] = $this->_files[$name];
                }

                if ($content['name'] === $file) {
                    $return[$name] = $this->_files[$name];
                }
            }

            if (count($return) === 0) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The file '$file' was not found");
            }

            return $return;
        }

        return $this->_files;
    }

    public function setFiles($files = array())
    {
        if (count($files) === 0) {
            $this->_files = $_FILES;
        } else {
            $this->_files = $files;
        }
        return $this;
    }

    public function isValid($value, $file = null)
    {
        if (array_key_exists($value, $this->_files)) {
            $files[$value] = $this->_files[$value];
        } else {
            foreach ($this->_files as $file => $content) {
                if ($content['name'] === $value) {
                    $files[$file] = $this->_files[$file];
                }

                if ($content['tmp_name'] === $value) {
                    $files[$file] = $this->_files[$file];
                }
            }
        }

        if (empty($files)) {
            return $this->_throw($file, self::FILE_NOT_FOUND);
        }

        foreach ($files as $file => $content) {
            $this->_value = $file;
            switch($content['error']) {
                case 0:
                    if (!is_uploaded_file($content['tmp_name'])) {
                        $this->_throw($file, self::ATTACK);
                    }
                    break;

                case 1:
                    $this->_throw($file, self::INI_SIZE);
                    break;

                case 2:
                    $this->_throw($file, self::FORM_SIZE);
                    break;

                case 3:
                    $this->_throw($file, self::PARTIAL);
                    break;

                case 4:
                    $this->_throw($file, self::NO_FILE);
                    break;

                case 6:
                    $this->_throw($file, self::NO_TMP_DIR);
                    break;

                case 7:
                    $this->_throw($file, self::CANT_WRITE);
                    break;

                case 8:
                    $this->_throw($file, self::EXTENSION);
                    break;

                default:
                    $this->_throw($file, self::UNKNOWN);
                    break;
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        } else {
            return true;
        }
    }

    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            if (is_array($file) and !empty($file['name'])) {
                $this->_value = $file['name'];
            }
        }

        $this->_error($errorType);
        return false;
    }
}
