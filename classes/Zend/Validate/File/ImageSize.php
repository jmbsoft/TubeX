<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_File_ImageSize extends Zend_Validate_Abstract
{

    const WIDTH_TOO_BIG    = 'fileImageSizeWidthTooBig';
    const WIDTH_TOO_SMALL  = 'fileImageSizeWidthTooSmall';
    const HEIGHT_TOO_BIG   = 'fileImageSizeHeightTooBig';
    const HEIGHT_TOO_SMALL = 'fileImageSizeHeightTooSmall';
    const NOT_DETECTED     = 'fileImageSizeNotDetected';
    const NOT_READABLE     = 'fileImageSizeNotReadable';

    protected $_messageTemplates = array(
        self::WIDTH_TOO_BIG    => "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected",
        self::WIDTH_TOO_SMALL  => "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected",
        self::HEIGHT_TOO_BIG   => "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected",
        self::HEIGHT_TOO_SMALL => "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected",
        self::NOT_DETECTED     => "The size of image '%value%' could not be detected",
        self::NOT_READABLE     => "The image '%value%' can not be read"
    );

    protected $_messageVariables = array(
        'minwidth'  => '_minwidth',
        'maxwidth'  => '_maxwidth',
        'minheight' => '_minheight',
        'maxheight' => '_maxheight',
        'width'     => '_width',
        'height'    => '_height'
    );

    protected $_minwidth;

    protected $_maxwidth;

    protected $_minheight;

    protected $_maxheight;

    protected $_width;

    protected $_height;

    public function __construct($options)
    {
        $minwidth  = 0;
        $minheight = 0;
        $maxwidth  = null;
        $maxheight = null;

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (1 < func_num_args()) {
            trigger_error('Multiple constructor options are deprecated in favor of a single options array', E_USER_NOTICE);
            if (!is_array($options)) {
                $options = array('minwidth' => $options);
            }
            $argv = func_get_args();
            array_shift($argv);
            $options['minheight'] = array_shift($argv);
            if (!empty($argv)) {
                $options['maxwidth'] = array_shift($argv);
                if (!empty($argv)) {
                    $options['maxheight'] = array_shift($argv);
                }
            }
        } else if (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        if (isset($options['minheight']) || isset($options['minwidth'])) {
            $this->setImageMin($options);
        }

        if (isset($options['maxheight']) || isset($options['maxwidth'])) {
            $this->setImageMax($options);
        }
    }

    public function getImageMin()
    {
        return array('minwidth' => $this->_minwidth, 'minheight' => $this->_minheight);
    }

    public function getImageMax()
    {
        return array('maxwidth' => $this->_maxwidth, 'maxheight' => $this->_maxheight);
    }

    public function getImageWidth()
    {
        return array('minwidth' => $this->_minwidth, 'maxwidth' => $this->_maxwidth);
    }

    public function getImageHeight()
    {
        return array('minheight' => $this->_minheight, 'maxheight' => $this->_maxheight);
    }

    public function setImageMin($options)
    {
        if (isset($options['minwidth'])) {
            if (($this->_maxwidth !== null) and ($options['minwidth'] > $this->_maxwidth)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The minimum image width must be less than or equal to the "
                    . " maximum image width, but {$options['minwidth']} > {$this->_maxwidth}");
            }
        }

        if (isset($options['maxheight'])) {
            if (($this->_maxheight !== null) and ($options['minheight'] > $this->_maxheight)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The minimum image height must be less than or equal to the "
                    . " maximum image height, but {$options['minheight']} > {$this->_maxheight}");
            }
        }

        if (isset($options['minwidth'])) {
            $this->_minwidth  = (int) $options['minwidth'];
        }

        if (isset($options['minheight'])) {
            $this->_minheight = (int) $options['minheight'];
        }

        return $this;
    }

    public function setImageMax($options)
    {
        if (isset($options['maxwidth'])) {
            if (($this->_minwidth !== null) and ($options['maxwidth'] < $this->_minwidth)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The maximum image width must be greater than or equal to the "
                    . "minimum image width, but {$options['maxwidth']} < {$this->_minwidth}");
            }
        }

        if (isset($options['maxheight'])) {
            if (($this->_minheight !== null) and ($options['maxheight'] < $this->_minheight)) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The maximum image height must be greater than or equal to the "
                    . "minimum image height, but {$options['maxheight']} < {$this->_minwidth}");
            }
        }

        if (isset($options['maxwidth'])) {
            $this->_maxwidth  = (int) $options['maxwidth'];
        }

        if (isset($options['maxheight'])) {
            $this->_maxheight = (int) $options['maxheight'];
        }

        return $this;
    }

    public function setImageWidth($options)
    {
        $this->setImageMin($options);
        $this->setImageMax($options);

        return $this;
    }

    public function setImageHeight($options)
    {
        $this->setImageMin($options);
        $this->setImageMax($options);

        return $this;
    }

    public function isValid($value, $file = null)
    {
        // Is file readable ?
        require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        $size = @getimagesize($value);
        $this->_setValue($file);

        if (empty($size) or ($size[0] === 0) or ($size[1] === 0)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        $this->_width  = $size[0];
        $this->_height = $size[1];
        if ($this->_width < $this->_minwidth) {
            $this->_throw($file, self::WIDTH_TOO_SMALL);
        }

        if (($this->_maxwidth !== null) and ($this->_maxwidth < $this->_width)) {
            $this->_throw($file, self::WIDTH_TOO_BIG);
        }

        if ($this->_height < $this->_minheight) {
            $this->_throw($file, self::HEIGHT_TOO_SMALL);
        }

        if (($this->_maxheight !== null) and ($this->_maxheight < $this->_height)) {
            $this->_throw($file, self::HEIGHT_TOO_BIG);
        }

        if (count($this->_messages) > 0) {
            return false;
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
