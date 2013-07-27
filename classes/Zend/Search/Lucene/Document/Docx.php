<?php


require_once 'Zend/Search/Lucene/Document/OpenXml.php';

if (class_exists('ZipArchive', false)) {

class Zend_Search_Lucene_Document_Docx extends Zend_Search_Lucene_Document_OpenXml {

    const SCHEMA_WORDPROCESSINGML = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private function __construct($fileName, $storeContent) {
        // Document data holders
        $documentBody = array();
        $coreProperties = array();

        // Open OpenXML package
        $package = new ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $relations = simplexml_load_string($package->getFromName('_rels/.rels'));
        foreach($relations->Relationship as $rel) {
            if ($rel ["Type"] == Zend_Search_Lucene_Document_OpenXml::SCHEMA_OFFICEDOCUMENT) {
                // Found office document! Read in contents...
                $contents = simplexml_load_string($package->getFromName(
                                                                $this->absoluteZipPath(dirname($rel['Target'])
                                                              . '/'
                                                              . basename($rel['Target']))
                                                                       ));

                $contents->registerXPathNamespace('w', Zend_Search_Lucene_Document_Docx::SCHEMA_WORDPROCESSINGML);
                $paragraphs = $contents->xpath('//w:body/w:p');

                foreach ($paragraphs as $paragraph) {
                    $runs = $paragraph->xpath('.//w:r/*[name() = "w:t" or name() = "w:br"]');

                    if ($runs === false) {
                    	// Paragraph doesn't contain any text or breaks
                    	continue;
                    }

                    foreach ($runs as $run) {
                     if ($run->getName() == 'br') {
                         // Break element
                         $documentBody[] = ' ';
                     } else {
                     	$documentBody[] = (string)$run;
                     }
                    }

                    // Add space after each paragraph. So they are not bound together.
                    $documentBody[] = ' ';
                }

                break;
            }
        }

        // Read core properties
        $coreProperties = $this->extractMetaData($package);

        // Close file
        $package->close();

        // Store filename
        $this->addField(Zend_Search_Lucene_Field::Text('filename', $fileName, 'UTF-8'));

        // Store contents
        if ($storeContent) {
            $this->addField(Zend_Search_Lucene_Field::Text('body', implode('', $documentBody), 'UTF-8'));
        } else {
            $this->addField(Zend_Search_Lucene_Field::UnStored('body', implode('', $documentBody), 'UTF-8'));
        }

        // Store meta data properties
        foreach ($coreProperties as $key => $value) {
            $this->addField(Zend_Search_Lucene_Field::Text($key, $value, 'UTF-8'));
        }

        // Store title (if not present in meta data)
        if (! isset($coreProperties['title'])) {
            $this->addField(Zend_Search_Lucene_Field::Text('title', $fileName, 'UTF-8'));
        }
    }

    public static function loadDocxFile($fileName, $storeContent = false) {
        if (!is_readable($fileName)) {
        	require_once 'Zend/Search/Lucene/Document/Exception.php';
        	throw new Zend_Search_Lucene_Document_Exception('Provided file \'' . $fileName . '\' is not readable.');
        }

    	return new Zend_Search_Lucene_Document_Docx($fileName, $storeContent);
    }
}

} // end if (class_exists('ZipArchive'))
