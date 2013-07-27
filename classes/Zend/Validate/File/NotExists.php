<?php


require_once 'Zend/Validate/File/Exists.php';

class Zend_Validate_File_NotExists extends Zend_Validate_File_Exists
{

    const DOES_EXIST = 'fileNotExistsDoesExist';

    protected $_messageTemplates = array(
        self::DOES_EXIST => "The file '%value%' does exist"
    );

    public function isValid($value, $file = null)
    {
        $directories = $this->getDirectory(true);
        if (($file !== null) and (!empty($file['destination']))) {
            $directories[] = $file['destination'];
        } else if (!isset($file['name'])) {
            $file['name'] = $value;
        }

        foreach ($directories as $directory) {
            if (empty($directory)) {
                continue;
            }

            $check = true;
            if (file_exists($directory . DIRECTORY_SEPARATOR . $file['name'])) {
                return $this->_throw($file, self::DOES_EXIST);
            }
        }

        if (!isset($check)) {
            return $this->_throw($file, self::DOES_EXIST);
        }

        return true;
    }
}
