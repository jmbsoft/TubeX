<?php


require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Extension_Rating extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rating';
    protected $_min = null;
    protected $_max = null;
    protected $_numRaters = null;
    protected $_average = null;
    protected $_value = null;

    public function __construct($average = null, $min = null,
            $max = null, $numRaters = null, $value = null)
    {
        parent::__construct();
        $this->_average = $average;
        $this->_min = $min;
        $this->_max = $max;
        $this->_numRaters = $numRaters;
        $this->_value = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_min !== null) {
            $element->setAttribute('min', $this->_min);
        }
        if ($this->_max !== null) {
            $element->setAttribute('max', $this->_max);
        }
        if ($this->_numRaters !== null) {
            $element->setAttribute('numRaters', $this->_numRaters);
        }
        if ($this->_average !== null) {
            $element->setAttribute('average', $this->_average);
        }
        if ($this->_value !== null) {
            $element->setAttribute('value', $this->_value);
        }

        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'min':
                $this->_min = $attribute->nodeValue;
                break;
            case 'max':
                $this->_max = $attribute->nodeValue;
                break;
            case 'numRaters':
                $this->_numRaters = $attribute->nodeValue;
                break;
            case 'average':
                $this->_average = $attribute->nodeValue;
                break;
            case 'value':
                $this->_value = $attribute->nodeValue;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getMin()
    {
        return $this->_min;
    }

    public function setMin($value)
    {
        $this->_min = $value;
        return $this;
    }

    public function getNumRaters()
    {
        return $this->_numRaters;
    }

    public function setNumRaters($value)
    {
        $this->_numRaters = $value;
        return $this;
    }

    public function getAverage()
    {
        return $this->_average;
    }

    public function setAverage($value)
    {
        $this->_average = $value;
        return $this;
    }

    public function getMax()
    {
        return $this->_max;
    }

    public function setMax($value)
    {
        $this->_max = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

}
