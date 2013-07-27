<?php


require_once 'Zend/Search/Lucene/Index/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry/Term.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry/Phrase.php';

require_once 'Zend/Search/Lucene/Search/QueryEntry/Subquery.php';

abstract class Zend_Search_Lucene_Search_QueryEntry
{

    protected $_boost = 1.0;

    abstract public function processFuzzyProximityModifier($parameter = null);

    abstract public function getQuery($encoding);

    public function boost($boostFactor)
    {
        $this->_boost *= $boostFactor;
    }


}
