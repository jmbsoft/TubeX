<?php


class Zend_Search_Lucene_Analysis_Token
{

    private $_termText;

    private $_startOffset;

    private $_endOffset;

    private $_positionIncrement;

    public function __construct($text, $start, $end)
    {
        $this->_termText    = $text;
        $this->_startOffset = $start;
        $this->_endOffset   = $end;

        $this->_positionIncrement = 1;
    }

    public function setPositionIncrement($positionIncrement)
    {
        $this->_positionIncrement = $positionIncrement;
    }

    public function getPositionIncrement()
    {
        return $this->_positionIncrement;
    }

    public function getTermText()
    {
        return $this->_termText;
    }

    public function getStartOffset()
    {
        return $this->_startOffset;
    }

    public function getEndOffset()
    {
        return $this->_endOffset;
    }
}

