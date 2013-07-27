<?php


require_once 'Zend/Validate/File/MimeType.php';

class Zend_Validate_File_ExcludeMimeType extends Zend_Validate_File_MimeType
{
    const FALSE_TYPE   = 'fileExcludeMimeTypeFalse';
    const NOT_DETECTED = 'fileExcludeMimeTypeNotDetected';
    const NOT_READABLE = 'fileExcludeMimeTypeNotReadable';

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        if ($file !== null) {
            if (class_exists('finfo', false) && defined('MAGIC')) {
                $mime = new finfo(FILEINFO_MIME);
                $this->_type = $mime->file($value);
                unset($mime);
            } elseif (function_exists('mime_content_type') && ini_get('mime_magic.magicfile')) {
                $this->_type = mime_content_type($value);
            } else {
                $this->_type = $file['type'];
            }
        }

        if (empty($this->_type)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $mimetype = $this->getMimeType(true);
        if (in_array($this->_type, $mimetype)) {
            return $this->_throw($file, self::FALSE_TYPE);
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        foreach($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return $this->_throw($file, self::FALSE_TYPE);
            }
        }

        return true;
    }
}
