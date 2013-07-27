<?php


require_once 'Zend/Search/Lucene/Interface.php';

class Zend_Search_Lucene_Proxy implements Zend_Search_Lucene_Interface
{

    private $_index;

    public function __construct(Zend_Search_Lucene_Interface $index)
    {
        $this->_index = $index;
        $this->_index->addReference();
    }

    public function __destruct()
    {
        if ($this->_index !== null) {
            // This code is invoked if Zend_Search_Lucene_Interface object constructor throws an exception
            $this->_index->removeReference();
        }
        $this->_index = null;
    }

    public static function getActualGeneration(Zend_Search_Lucene_Storage_Directory $directory)
    {
        Zend_Search_Lucene::getActualGeneration($directory);
    }

    public static function getSegmentFileName($generation)
    {
        Zend_Search_Lucene::getSegmentFileName($generation);
    }

    public function getFormatVersion()
    {
        return $this->_index->getFormatVersion();
    }

    public function setFormatVersion($formatVersion)
    {
        $this->_index->setFormatVersion($formatVersion);
    }

    public function getDirectory()
    {
        return $this->_index->getDirectory();
    }

    public function count()
    {
        return $this->_index->count();
    }

    public function maxDoc()
    {
        return $this->_index->maxDoc();
    }

    public function numDocs()
    {
        return $this->_index->numDocs();
    }

    public function isDeleted($id)
    {
        return $this->_index->isDeleted($id);
    }

    public static function setDefaultSearchField($fieldName)
    {
        Zend_Search_Lucene::setDefaultSearchField($fieldName);
    }

    public static function getDefaultSearchField()
    {
        return Zend_Search_Lucene::getDefaultSearchField();
    }

    public static function setResultSetLimit($limit)
    {
        Zend_Search_Lucene::setResultSetLimit($limit);
    }

    public static function getResultSetLimit()
    {
        return Zend_Search_Lucene::getResultSetLimit();
    }

    public function getMaxBufferedDocs()
    {
        return $this->_index->getMaxBufferedDocs();
    }

    public function setMaxBufferedDocs($maxBufferedDocs)
    {
        $this->_index->setMaxBufferedDocs($maxBufferedDocs);
    }

    public function getMaxMergeDocs()
    {
        return $this->_index->getMaxMergeDocs();
    }

    public function setMaxMergeDocs($maxMergeDocs)
    {
        $this->_index->setMaxMergeDocs($maxMergeDocs);
    }

    public function getMergeFactor()
    {
        return $this->_index->getMergeFactor();
    }

    public function setMergeFactor($mergeFactor)
    {
        $this->_index->setMergeFactor($mergeFactor);
    }

    public function find($query)
    {
        // actual parameter list
        $parameters = func_get_args();

        // invoke $this->_index->find() method with specified parameters
        return call_user_func_array(array(&$this->_index, 'find'), $parameters);
    }

    public function getFieldNames($indexed = false)
    {
        return $this->_index->getFieldNames($indexed);
    }

    public function getDocument($id)
    {
        return $this->_index->getDocument($id);
    }

    public function hasTerm(Zend_Search_Lucene_Index_Term $term)
    {
        return $this->_index->hasTerm($term);
    }

    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termDocs($term, $docsFilter);
    }

    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termDocsFilter($term, $docsFilter);
    }

    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termFreqs($term, $docsFilter);
    }

    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null)
    {
        return $this->_index->termPositions($term, $docsFilter);
    }

    public function docFreq(Zend_Search_Lucene_Index_Term $term)
    {
        return $this->_index->docFreq($term);
    }

    public function getSimilarity()
    {
        return $this->_index->getSimilarity();
    }

    public function norm($id, $fieldName)
    {
        return $this->_index->norm($id, $fieldName);
    }

    public function hasDeletions()
    {
        return $this->_index->hasDeletions();
    }

    public function delete($id)
    {
        return $this->_index->delete($id);
    }

    public function addDocument(Zend_Search_Lucene_Document $document)
    {
        $this->_index->addDocument($document);
    }

    public function commit()
    {
        $this->_index->commit();
    }

    public function optimize()
    {
        $this->_index->optimize();
    }

    public function terms()
    {
        return $this->_index->terms();
    }

    public function resetTermsStream()
    {
        $this->_index->resetTermsStream();
    }

    public function skipTo(Zend_Search_Lucene_Index_Term $prefix)
    {
        return $this->_index->skipTo($prefix);
    }

    public function nextTerm()
    {
        return $this->_index->nextTerm();
    }

    public function currentTerm()
    {
        return $this->_index->currentTerm();
    }

    public function closeTermsStream()
    {
        $this->_index->closeTermsStream();
    }

    public function undeleteAll()
    {
        return $this->_index->undeleteAll();
    }

    public function addReference()
    {
        return $this->_index->addReference();
    }

    public function removeReference()
    {
        return $this->_index->removeReference();
    }
}
