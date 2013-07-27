<?php


require_once 'Zend/Search/Lucene/Document.php';

if (class_exists('ZipArchive', false)) {

abstract class Zend_Search_Lucene_Document_OpenXml extends Zend_Search_Lucene_Document
{

    const SCHEMA_RELATIONSHIP = 'http://schemas.openxmlformats.org/package/2006/relationships';

    const SCHEMA_OFFICEDOCUMENT = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';

    const SCHEMA_COREPROPERTIES = 'http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties';

    const SCHEMA_DUBLINCORE = 'http://purl.org/dc/elements/1.1/';

    const SCHEMA_DUBLINCORETERMS = 'http://purl.org/dc/terms/';

    protected function extractMetaData(ZipArchive $package)
    {
        // Data holders
        $coreProperties = array();
        
        // Read relations and search for core properties
        $relations = simplexml_load_string($package->getFromName("_rels/.rels"));
        foreach ($relations->Relationship as $rel) {
            if ($rel["Type"] == Zend_Search_Lucene_Document_OpenXml::SCHEMA_COREPROPERTIES) {
                // Found core properties! Read in contents...
                $contents = simplexml_load_string(
                    $package->getFromName(dirname($rel["Target"]) . "/" . basename($rel["Target"]))
                );

                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_DUBLINCORE) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_COREPROPERTIES) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
                foreach ($contents->children(Zend_Search_Lucene_Document_OpenXml::SCHEMA_DUBLINCORETERMS) as $child) {
                    $coreProperties[$child->getName()] = (string)$child;
                }
            }
        }
        
        return $coreProperties;
    }

    protected function absoluteZipPath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode('/', $absolutes);
    }
}

} // end if (class_exists('ZipArchive'))
