<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_Extension extends Zend_Validate_Abstract
{

    const FALSE_EXTENSION = 'fileExtensionFalse';
    const NOT_FOUND       = 'fileExtensionNotFound';

    protected $_messageTemplates = array(
        self::FALSE_EXTENSION => "The file '%value%' has a false extension",
        self::NOT_FOUND       => "The file '%value%' was not found"
    );

    protected $_extension = '';

    protected $_case = false;

    protected $_messageVariables = array(
        'extension' => '_extension'
    );

    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (1 < func_num_args()) {
            trigger_error('Multiple arguments to constructor are deprecated in favor of options array', E_USER_NOTICE);
            $case = func_get_arg(1);
            $this->setCase($case);
        }

        if (is_array($options) and isset($options['case'])) {
            $this->setCase($options['case']);
            unset($options['case']);
        }

        $this->setExtension($options);
    }

    public function getCase()
    {
        return $this->_case;
    }

    public function setCase($case)
    {
        $this->_case = (boolean) $case;
        return $this;
    }

    public function getExtension()
    {
        $extension = explode(',', $this->_extension);

        return $extension;
    }

    public function setExtension($extension)
    {
        $this->_extension = null;
        $this->addExtension($extension);
        return $this;
    }

    public function addExtension($extension)
    {
        $extensions = $this->getExtension();
        if (is_string($extension)) {
            $extension = explode(',', $extension);
        }

        foreach ($extension as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }

            $extensions[] = trim($content);
        }
        $extensions = array_unique($extensions);

        // Sanity check to ensure no empty values
        foreach ($extensions as $key => $ext) {
            if (empty($ext)) {
                unset($extensions[$key]);
            }
        }

        $this->_extension = implode(',', $extensions);

        return $this;
    }

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

        if ($this->_case && (in_array($info['extension'], $extensions))) {
            return true;
        } else if (!$this->getCase()) {
            foreach ($extensions as $extension) {
                if (strtolower($extension) == strtolower($info['extension'])) {
                    return true;
                }
            }
        }

        return $this->_throw($file, self::FALSE_EXTENSION);
    }

    protected function _throw($file, $errorType)
    {
        if (null !== $file) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}
