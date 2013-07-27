<?php


require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry.php';

class Zend_Search_Lucene_Search_QueryEntry_Subquery extends Zend_Search_Lucene_Search_QueryEntry
{

    private $_query;

    public function __construct(Zend_Search_Lucene_Search_Query $query)
    {
        $this->_query = $query;
    }

    public function processFuzzyProximityModifier($parameter = null)
    {
        require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
        throw new Zend_Search_Lucene_Search_QueryParserException('\'~\' sign must follow term or phrase');
    }

    public function getQuery($encoding)
    {
        $this->_query->setBoost($this->_boost);

        return $this->_query;
    }
}
