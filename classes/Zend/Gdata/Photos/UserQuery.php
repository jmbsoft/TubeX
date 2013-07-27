<?php


require_once('Zend/Gdata/Gapps/Query.php');

class Zend_Gdata_Photos_UserQuery extends Zend_Gdata_Query
{

    protected $_projection = 'api';

    protected $_type = 'feed';

    protected $_user = Zend_Gdata_Photos::DEFAULT_USER;

    public function __construct()
    {
        parent::__construct();
    }

    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    public function getProjection()
    {
        return $this->_projection;
    }

    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    public function getType()
    {
        return $this->_type;
    }

     public function setUser($value)
     {
         if ($value !== null) {
             $this->_user = $value;
         } else {
             $this->_user = Zend_Gdata_Photos::DEFAULT_USER;
         }
     }

    public function getUser()
    {
        return $this->_user;
    }

     public function setAccess($value)
     {
         if ($value !== null) {
             $this->_params['access'] = $value;
         } else {
             unset($this->_params['access']);
         }
     }

    public function getAccess()
    {
        return $this->_params['access'];
    }

     public function setTag($value)
     {
         if ($value !== null) {
             $this->_params['tag'] = $value;
         } else {
             unset($this->_params['tag']);
         }
     }

    public function getTag()
    {
        return $this->_params['tag'];
    }

     public function setKind($value)
     {
         if ($value !== null) {
             $this->_params['kind'] = $value;
         } else {
             unset($this->_params['kind']);
         }
     }

    public function getKind()
    {
        return $this->_params['kind'];
    }

     public function setImgMax($value)
     {
         if ($value !== null) {
             $this->_params['imgmax'] = $value;
         } else {
             unset($this->_params['imgmax']);
         }
     }

    public function getImgMax()
    {
        return $this->_params['imgmax'];
    }

     public function setThumbsize($value)
     {
         if ($value !== null) {
             $this->_params['thumbsize'] = $value;
         } else {
             unset($this->_params['thumbsize']);
         }
     }

    public function getThumbsize()
    {
        return $this->_params['thumbsize'];
    }

    public function getQueryUrl($incomingUri = null)
    {
        $uri = Zend_Gdata_Photos::PICASA_BASE_URI;
        
        if ($this->getType() !== null) {
            $uri .= '/' . $this->getType();
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Type must be feed or entry, not null');
        }
        
        if ($this->getProjection() !== null) {
            $uri .= '/' . $this->getProjection();
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Projection must not be null');
        }
        
        if ($this->getUser() !== null) {
            $uri .= '/user/' . $this->getUser();
        } else {
            // Should never occur due to setter behavior
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'User must not be null');
        }
        
        $uri .= $incomingUri;
        $uri .= $this->getQueryString();
        return $uri;
    }

}
