<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Exists extends Zend_Validate_Abstract
{

    const DOES_NOT_EXIST = 'fileExistsDoesNotExist';

    protected $_messageTemplates = array(
        self::DOES_NOT_EXIST => "The file '%value%' does not exist"
    );

    protected $_directory = '';

    protected $_messageVariables = array(
        'directory' => '_directory'
    );

    public function __construct($directory = array())
    {
        if ($directory instanceof Zend_Config) {
            $directory = $directory->toArray();
        } else if (is_string($directory)) {
            $directory = explode(',', $directory);
        } else if (!is_array($directory)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $this->setDirectory($directory);
    }

    public function getDirectory($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $directory = (string) $this->_directory;
        if ($asArray) {
            $directory = explode(',', $directory);
        }

        return $directory;
    }

    public function setDirectory($directory)
    {
        $this->_directory = null;
        $this->addDirectory($directory);
        return $this;
    }

    public function addDirectory($directory)
    {
        $directories = $this->getDirectory(true);

        if (is_string($directory)) {
            $directory = explode(',', $directory);
        } else if (!is_array($directory)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        foreach ($directory as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }

            $directories[] = trim($content);
        }
        $directories = array_unique($directories);

        // Sanity check to ensure no empty values
        foreach ($directories as $key => $dir) {
            if (empty($dir)) {
                unset($directories[$key]);
            }
        }

        $this->_directory = implode(',', $directories);

        return $this;
    }

    public function isValid($value, $file = null)
    {
        $directories = $this->getDirectory(true);
        if (($file !== null) and (!empty($file['destination']))) {
            $directories[] = $file['destination'];
        } else if (!isset($file['name'])) {
            $file['name'] = $value;
        }

        $check = false;
        foreach ($directories as $directory) {
            if (empty($directory)) {
                continue;
            }

            $check = true;
            if (!file_exists($directory . DIRECTORY_SEPARATOR . $file['name'])) {
                return $this->_throw($file, self::DOES_NOT_EXIST);
            }
        }

        if (!$check) {
            return $this->_throw($file, self::DOES_NOT_EXIST);
        }

        return true;
    }

    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}
