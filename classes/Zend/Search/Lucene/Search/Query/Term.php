<?php


require_once 'Zend/Search/Lucene/Search/Query.php';

require_once 'Zend/Search/Lucene/Search/Weight/Term.php';

class Zend_Search_Lucene_Search_Query_Term extends Zend_Search_Lucene_Search_Query
{

    private $_term;

    private $_docVector = null;

    private $_termFreqs;

    public function __construct(Zend_Search_Lucene_Index_Term $term)
    {
        $this->_term = $term;
    }

    public function rewrite(Zend_Search_Lucene_Interface $index)
    {
        if ($this->_term->field != null) {
            return $this;
        } else {
            $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
            $query->setBoost($this->getBoost());

            foreach ($index->getFieldNames(true) as $fieldName) {
                $term = new Zend_Search_Lucene_Index_Term($this->_term->text, $fieldName);

                $query->addTerm($term);
            }

            return $query->rewrite($index);
        }
    }

    public function optimize(Zend_Search_Lucene_Interface $index)
    {
        // Check, that index contains specified term
        if (!$index->hasTerm($this->_term)) {
            return new Zend_Search_Lucene_Search_Query_Empty();
        }

        return $this;
    }

    public function createWeight(Zend_Search_Lucene_Interface $reader)
    {
        $this->_weight = new Zend_Search_Lucene_Search_Weight_Term($this->_term, $this, $reader);
        return $this->_weight;
    }

    public function execute(Zend_Search_Lucene_Interface $reader, $docsFilter = null)
    {
        $this->_docVector = array_flip($reader->termDocs($this->_term, $docsFilter));
        $this->_termFreqs = $reader->termFreqs($this->_term, $docsFilter);

        // Initialize weight if it's not done yet
        $this->_initWeight($reader);
    }

    public function matchedDocs()
    {
        return $this->_docVector;
    }

    public function score($docId, Zend_Search_Lucene_Interface $reader)
    {
        if (isset($this->_docVector[$docId])) {
            return $reader->getSimilarity()->tf($this->_termFreqs[$docId]) *
                   $this->_weight->getValue() *
                   $reader->norm($docId, $this->_term->field) *
                   $this->getBoost();
        } else {
            return 0;
        }
    }

    public function getQueryTerms()
    {
        return array($this->_term);
    }

    public function getTerm()
    {
        return $this->_term;
    }

    public function getTerms()
    {
        return $this->_terms;
    }

    public function highlightMatchesDOM(Zend_Search_Lucene_Document_Html $doc, &$colorIndex)
    {
        $doc->highlight($this->_term->text, $this->_getHighlightColor($colorIndex));
    }

    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
        return (($this->_term->field === null)? '':$this->_term->field . ':') . $this->_term->text;
    }
}

