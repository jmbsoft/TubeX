<?php


require_once('Zend/Gdata/Photos/UserQuery.php');

class Zend_Gdata_Photos_AlbumQuery extends Zend_Gdata_Photos_UserQuery
{

    protected $_albumName = null;

    protected $_albumId = null;

     public function setAlbumName($value)
     {
         $this->_albumId = null;
         $this->_albumName = $value;
         
         return $this;
     }

    public function getAlbumName()
    {
        return $this->_albumName;
    }

     public function setAlbumId($value)
     {
         $this->_albumName = null;
         $this->_albumId = $value;
         
         return $this;
     }

    public function getAlbumId()
    {
        return $this->_albumId;
    }

    public function getQueryUrl($incomingUri = '')
    {
        $uri = '';
        if ($this->getAlbumName() !== null && $this->getAlbumId() === null) {
            $uri .= '/album/' . $this->getAlbumName();
        } elseif ($this->getAlbumName() === null && $this->getAlbumId() !== null) {
            $uri .= '/albumid/' . $this->getAlbumId();
        } elseif ($this->getAlbumName() !== null && $this->getAlbumId() !== null) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'AlbumName and AlbumId cannot both be non-null');
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'AlbumName and AlbumId cannot both be null');
        }
        $uri .= $incomingUri;
        return parent::getQueryUrl($uri);
    }

}
