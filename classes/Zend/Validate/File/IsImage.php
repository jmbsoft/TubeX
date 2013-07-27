<?php


require_once 'Zend/Validate/File/MimeType.php';

class Zend_Validate_File_IsImage extends Zend_Validate_File_MimeType
{

    const FALSE_TYPE   = 'fileIsImageFalseType';
    const NOT_DETECTED = 'fileIsImageNotDetected';
    const NOT_READABLE = 'fileIsImageNotReadable';

    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "The file '%value%' is no image, '%type%' detected",
        self::NOT_DETECTED => "The mimetype of file '%value%' has not been detected",
        self::NOT_READABLE => "The file '%value%' can not be read"
    );

    public function __construct($mimetype = array())
    {
        if (empty($mimetype)) {
            $mimetype = array(
                'image/x-quicktime',
                'image/jp2',
                'image/x-xpmi',
                'image/x-portable-bitmap',
                'image/x-portable-greymap',
                'image/x-portable-pixmap',
                'image/x-niff',
                'image/tiff',
                'image/png',
                'image/x-unknown',
                'image/gif',
                'image/x-ms-bmp',
                'application/dicom',
                'image/vnd.adobe.photoshop',
                'image/vnd.djvu',
                'image/x-cpi',
                'image/jpeg',
                'image/x-ico',
                'image/x-coreldraw',
                'image/svg+xml'
            );
        }

        $this->setMimeType($mimetype);
    }

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

        $compressions = $this->getMimeType(true);
        if (in_array($this->_type, $compressions)) {
            return true;
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        foreach($compressions as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_TYPE);
    }
}
