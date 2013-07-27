<?php


interface Zend_Search_Lucene_Interface
{

    public static function getActualGeneration(Zend_Search_Lucene_Storage_Directory $directory);

    public static function getSegmentFileName($generation);

    public function getFormatVersion();

    public function setFormatVersion($formatVersion);

    public function getDirectory();

    public function count();

    public function maxDoc();

    public function numDocs();

    public function isDeleted($id);

    public static function setDefaultSearchField($fieldName);

    public static function getDefaultSearchField();

    public static function setResultSetLimit($limit);

    public static function getResultSetLimit();

    public function getMaxBufferedDocs();

    public function setMaxBufferedDocs($maxBufferedDocs);

    public function getMaxMergeDocs();

    public function setMaxMergeDocs($maxMergeDocs);

    public function getMergeFactor();

    public function setMergeFactor($mergeFactor);

    public function find($query);

    public function getFieldNames($indexed = false);

    public function getDocument($id);

    public function hasTerm(Zend_Search_Lucene_Index_Term $term);

    public function termDocs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    public function termDocsFilter(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    public function termFreqs(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    public function termPositions(Zend_Search_Lucene_Index_Term $term, $docsFilter = null);

    public function docFreq(Zend_Search_Lucene_Index_Term $term);

    public function getSimilarity();

    public function norm($id, $fieldName);

    public function hasDeletions();

    public function delete($id);

    public function addDocument(Zend_Search_Lucene_Document $document);

    public function commit();

    public function optimize();

    public function terms();

    public function resetTermsStream();

    public function skipTo(Zend_Search_Lucene_Index_Term $prefix);

    public function nextTerm();

    public function currentTerm();

    public function closeTermsStream();

    public function undeleteAll();

    public function addReference();

    public function removeReference();
}
