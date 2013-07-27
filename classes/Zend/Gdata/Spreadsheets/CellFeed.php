<?php


require_once 'Zend/Gdata/Feed.php';

require_once 'Zend/Gdata/Spreadsheets/Extension/RowCount.php';

require_once 'Zend/Gdata/Spreadsheets/Extension/ColCount.php';

class Zend_Gdata_Spreadsheets_CellFeed extends Zend_Gdata_Feed
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_CellEntry';

    protected $_feedClassName = 'Zend_Gdata_Spreadsheets_CellFeed';

    protected $_rowCount = null;

    protected $_colCount = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->rowCount != null) {
            $element->appendChild($this->_rowCount->getDOM($element->ownerDocument));
        }
        if ($this->colCount != null) {
            $element->appendChild($this->_colCount->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gs') . ':' . 'rowCount';
                $rowCount = new Zend_Gdata_Spreadsheets_Extension_RowCount();
                $rowCount->transferFromDOM($child);
                $this->_rowCount = $rowCount;
                break;
            case $this->lookupNamespace('gs') . ':' . 'colCount';
                $colCount = new Zend_Gdata_Spreadsheets_Extension_ColCount();
                $colCount->transferFromDOM($child);
                $this->_colCount = $colCount;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;
        }
    }

    public function getRowCount()
    {
        return $this->_rowCount;
    }

    public function getColumnCount()
    {
        return $this->_colCount;
    }

    public function setRowCount($rowCount)
    {
        $this->_rowCount = $rowCount;
        return $this;
    }

    public function setColumnCount($colCount)
    {
        $this->_colCount = $colCount;
        return $this;
    }

}
