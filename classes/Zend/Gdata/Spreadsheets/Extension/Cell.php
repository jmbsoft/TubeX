<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Extension.php';

class Zend_Gdata_Spreadsheets_Extension_Cell extends Zend_Gdata_Extension
{
    protected $_rootElement = 'cell';
    protected $_rootNamespace = 'gs';

    protected $_row = null;

    protected $_col = null;

    protected $_inputValue = null;

    protected $_numericValue = null;

    public function __construct($text = null, $row = null, $col = null, $inputValue = null, $numericValue = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_row = $row;
        $this->_col = $col;
        $this->_inputValue = $inputValue;
        $this->_numericValue = $numericValue;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        $element->setAttribute('row', $this->_row);
        $element->setAttribute('col', $this->_col);
        if ($this->_inputValue) $element->setAttribute('inputValue', $this->_inputValue);
        if ($this->_numericValue) $element->setAttribute('numericValue', $this->_numericValue);
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'row':
            $this->_row = $attribute->nodeValue;
            break;
        case 'col':
            $this->_col = $attribute->nodeValue;
            break;
        case 'inputValue':
            $this->_inputValue = $attribute->nodeValue;
            break;
        case 'numericValue':
            $this->_numericValue = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getRow()
    {
        return $this->_row;
    }

    public function getColumn()
    {
        return $this->_col;
    }

    public function getInputValue()
    {
        return $this->_inputValue;
    }

    public function getNumericValue()
    {
        return $this->_numericValue;
    }

    public function setRow($row)
    {
        $this->_row = $row;
        return $this;
    }

    public function setColumn($col)
    {
        $this->_col = $col;
        return $this;
    }

    public function setInputValue($inputValue)
    {
        $this->_inputValue = $inputValue;
        return $this;
    }

    public function setNumericValue($numericValue)
    {
        $this->_numericValue = $numericValue;
    }

}
