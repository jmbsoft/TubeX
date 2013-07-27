<?php


require_once('Zend/Gdata/Photos/AlbumQuery.php');

class Zend_Gdata_Photos_PhotoQuery extends Zend_Gdata_Photos_AlbumQuery
{

    protected $_photoId = null;

     public function setPhotoId($value)
     {
         $this->_photoId = $value;
     }

    public function getPhotoId()
    {
        return $this->_photoId;
    }

    public function getQueryUrl($incomingUri = '')
    {
        $uri = '';
        if ($this->getPhotoId() !== null) {
            $uri .= '/photoid/' . $this->getPhotoId();
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'PhotoId cannot be null');
        }
        $uri .= $incomingUri;
        return parent::getQueryUrl($uri);
    }

}
