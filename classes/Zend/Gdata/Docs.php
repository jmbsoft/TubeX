<?php


require_once 'Zend/Gdata.php';

require_once 'Zend/Gdata/Docs/DocumentListFeed.php';

require_once 'Zend/Gdata/Docs/DocumentListEntry.php';

class Zend_Gdata_Docs extends Zend_Gdata
{

    const DOCUMENTS_LIST_FEED_URI = 'http://docs.google.com/feeds/documents/private/full';
    const AUTH_SERVICE_NAME = 'writely';

    protected $_defaultPostUri = self::DOCUMENTS_LIST_FEED_URI;

    private static $SUPPORTED_FILETYPES = array(
      'CSV'=>'text/csv',
      'DOC'=>'application/msword',
      'ODS'=>'application/vnd.oasis.opendocument.spreadsheet',
      'ODT'=>'application/vnd.oasis.opendocument.text',
      'RTF'=>'application/rtf',
      'SXW'=>'application/vnd.sun.xml.writer',
      'TXT'=>'text/plain',
      'XLS'=>'application/vnd.ms-excel');

    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Docs');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    public static function lookupMimeType($fileExtension) {
      return self::$SUPPORTED_FILETYPES[strtoupper($fileExtension)];
    }

    public function getDocumentListFeed($location = null)
    {
        if ($location === null) {
            $uri = self::DOCUMENTS_LIST_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Docs_DocumentListFeed');
    }

    public function getDocumentListEntry($location = null)
    {
        if ($location === null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Docs_DocumentListEntry');
    }

    public function getDoc($docId, $docType) {
        $location = 'http://docs.google.com/feeds/documents/private/full/' . 
            $docType . '%3A' . $docId;
        return $this->getDocumentListEntry($location);
    }

    public function getDocument($id) {
      return $this->getDoc($id, 'document');
    }

    public function getSpreadsheet($id) {
      return $this->getDoc($id, 'spreadsheet');
    }

    public function getPresentation($id) {
      return $this->getDoc($id, 'presentation');
    }

    public function uploadFile($fileLocation, $title=null, $mimeType=null, 
                               $uri=null)
    {
        // Set the URI to which the file will be uploaded.
        if ($uri === null) {
            $uri = $this->_defaultPostUri;
        }
        
        // Create the media source which describes the file.
        $fs = $this->newMediaFileSource($fileLocation);
        if ($title !== null) {
            $slugHeader = $title;
        } else {
            $slugHeader = $fileLocation;
        }
        
        // Set the slug header to tell the Google Documents server what the 
        // title of the document should be and what the file extension was 
        // for the original file.
        $fs->setSlug($slugHeader);

        // Set the mime type of the data.
        if ($mimeType === null) {
          $slugHeader =  $fs->getSlug();
          $filenameParts = explode('.', $slugHeader);
          $fileExtension = end($filenameParts);
          $mimeType = self::lookupMimeType($fileExtension);
        }
        
        // Set the mime type for the upload request.
        $fs->setContentType($mimeType);
        
        // Send the data to the server.
        return $this->insertDocument($fs, $uri);
    }

    public function insertDocument($data, $uri, 
        $className='Zend_Gdata_Docs_DocumentListEntry')
    {
        return $this->insertEntry($data, $uri, $className);
    }

}
