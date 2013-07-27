<?php


require_once 'Zend/Search/Lucene/Document/Html.php';

require_once 'Zend/Search/Lucene/Index/DocsFilter.php';

abstract class Zend_Search_Lucene_Search_Query
{

    private $_boost = 1;

    protected $_weight = null;

    private $_currentColorIndex = 0;

    private $_highlightColors = array('#66ffff', '#ff66ff', '#ffff66',
                                      '#ff8888', '#88ff88', '#8888ff',
                                      '#88dddd', '#dd88dd', '#dddd88',
                                      '#aaddff', '#aaffdd', '#ddaaff', '#ddffaa', '#ffaadd', '#ffddaa');

    public function getBoost()
    {
        return $this->_boost;
    }

    public function setBoost($boost)
    {
        $this->_boost = $boost;
    }

    abstract public function score($docId, Zend_Search_Lucene_Interface $reader);

    abstract public function matchedDocs();

    abstract public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null);

    abstract public function createWeight(Zend_Search_Lucene_Interface $reader);

    protected function _initWeight(Zend_Search_Lucene_Interface $reader)
    {
        // Check, that it's a top-level query and query weight is not initialized yet.
        if ($this->_weight !== null) {
            return $this->_weight;
        }

        $this->createWeight($reader);
        $sum = $this->_weight->sumOfSquaredWeights();
        $queryNorm = $reader->getSimilarity()->queryNorm($sum);
        $this->_weight->normalize($queryNorm);
    }

    abstract public function rewrite(Zend_Search_Lucene_Interface $index);

    abstract public function optimize(Zend_Search_Lucene_Interface $index);

    public function reset()
    {
        $this->_weight = null;
    }

    abstract public function __toString();

    abstract public function getQueryTerms();

    protected function _getHighlightColor(&$colorIndex)
    {
        $color = $this->_highlightColors[$colorIndex++];

        $colorIndex %= count($this->_highlightColors);

        return $color;
    }

    abstract public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex);

    public function highlightMatches($inputHTML)
    {
        $doc = Zend_Search_Lucene_Document_Html::loadHTML($inputHTML);

        $colorIndex = 0;
        $this->highlightMatchesDOM($doc, $colorIndex);

        return $doc->getHTML();
    }
}

