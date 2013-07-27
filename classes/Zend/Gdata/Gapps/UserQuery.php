<?php


require_once('Zend/Gdata/Gapps/Query.php');

class Zend_Gdata_Gapps_UserQuery extends Zend_Gdata_Gapps_Query
{

    protected $_username = null;

    public function __construct($domain = null, $username = null, 
            $startUsername = null)
    {
        parent::__construct($domain);
        $this->setUsername($username);
        $this->setStartUsername($startUsername);
    }

    public function setUsername($value)
    {
        $this->_username = $value;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setStartUsername($value)
    {
        if ($value !== null) {
            $this->_params['startUsername'] = $value;
        } else {
            unset($this->_params['startUsername']);
        }
    }

    public function getStartUsername()
    {
        if (array_key_exists('startUsername', $this->_params)) {
            return $this->_params['startUsername'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {
        $uri = $this->getBaseUrl();
        $uri .= Zend_Gdata_Gapps::APPS_USER_PATH;
        if ($this->_username !== null) {
            $uri .= '/' . $this->_username;
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
