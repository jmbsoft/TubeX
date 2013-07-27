<?php


require_once 'Zend/Validate/File/Extension.php';

class Zend_Validate_File_ExcludeExtension extends Zend_Validate_File_Extension
{

    const FALSE_EXTENSION = 'fileExcludeExtensionFalse';
    const NOT_FOUND       = 'fileExcludeExtensionNotFound';

    protected $_messageTemplates = array(
        self::FALSE_EXTENSION => "The file '%value%' has a false extension",
        self::NOT_FOUND       => "The file '%value%' was not found"
    );

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        if ($file !== null) {
            $info['extension'] = substr($file['name'], strrpos($file['name'], '.') + 1);
        } else {
            $info = pathinfo($value);
        }

        $extensions = $this->getExtension();

        if ($this->_case and (!in_array($info['extension'], $extensions))) {
            return true;
        } else if (!$this->_case) {
            $found = false;
            foreach ($extensions as $extension) {
                if (strtolower($extension) == strtolower($info['extension'])) {
                    $found = true;
                }
            }

            if (!$found) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_EXTENSION);
    }
}
