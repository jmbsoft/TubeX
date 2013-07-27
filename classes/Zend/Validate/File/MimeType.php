<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_MimeType extends Zend_Validate_Abstract
{

    const FALSE_TYPE   = 'fileMimeTypeFalse';
    const NOT_DETECTED = 'fileMimeTypeNotDetected';
    const NOT_READABLE = 'fileMimeTypeNotReadable';


    protected $_messageTemplates = array(
        self::FALSE_TYPE   => "The file '%value%' has a false mimetype of '%type%'",
        self::NOT_DETECTED => "The mimetype of file '%value%' could not been detected",
        self::NOT_READABLE => "The file '%value%' can not be read"
    );

    protected $_messageVariables = array(
        'type' => '_type'
    );

    protected $_type;

    protected $_mimetype;

    protected $_magicfile;

    public function __construct($mimetype)
    {
        if ($mimetype instanceof Zend_Config) {
            $mimetype = $mimetype->toArray();
        } elseif (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            $this->setMagicFile($mimetype['magicfile']);
        }

        $this->setMimeType($mimetype);
    }

    public function getMagicFile()
    {
        return $this->_magicfile;
    }

    public function setMagicFile($file)
    {
        if (empty($file)) {
            $this->_magicfile = null;
        } else if (!is_readable($file)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The given magicfile can not be read');
        } else {
            $this->_magicfile = (string) $file;
        }

        return $this;
    }

    public function getMimeType($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $mimetype = (string) $this->_mimetype;
        if ($asArray) {
            $mimetype = explode(',', $mimetype);
        }

        return $mimetype;
    }

    public function setMimeType($mimetype)
    {
        $this->_mimetype = null;
        $this->addMimeType($mimetype);
        return $this;
    }

    public function addMimeType($mimetype)
    {
        $mimetypes = $this->getMimeType(true);

        if (is_string($mimetype)) {
            $mimetype = explode(',', $mimetype);
        } elseif (!is_array($mimetype)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Invalid options to validator provided");
        }

        if (isset($mimetype['magicfile'])) {
            unset($mimetype['magicfile']);
        }

        foreach ($mimetype as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }
            $mimetypes[] = trim($content);
        }
        $mimetypes = array_unique($mimetypes);

        // Sanity check to ensure no empty values
        foreach ($mimetypes as $key => $mt) {
            if (empty($mt)) {
                unset($mimetypes[$key]);
            }
        }

        $this->_mimetype = implode(',', $mimetypes);

        return $this;
    }

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        if ($file !== null) {
            $mimefile = $this->getMagicFile();
            if (class_exists('finfo', false) && ((!empty($mimefile)) or (defined('MAGIC')))) {
                if (!empty($mimefile)) {
                    $mime = new finfo(FILEINFO_MIME, $mimefile);
                } else {
                    $mime = new finfo(FILEINFO_MIME);
                }

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
            return true;
        }

        $types = explode('/', $this->_type);
        $types = array_merge($types, explode('-', $this->_type));
        foreach($mimetype as $mime) {
            if (in_array($mime, $types)) {
                return true;
            }
        }

        return $this->_throw($file, self::FALSE_TYPE);
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
