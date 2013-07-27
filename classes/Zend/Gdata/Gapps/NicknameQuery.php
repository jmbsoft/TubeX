<?php


require_once('Zend/Gdata/Gapps/Query.php');

class Zend_Gdata_Gapps_NicknameQuery extends Zend_Gdata_Gapps_Query
{

    protected $_nickname = null;

    public function __construct($domain = null, $nickname = null, 
            $username = null, $startNickname = null)
    {
        parent::__construct($domain);
        $this->setNickname($nickname);
        $this->setUsername($username);
        $this->setStartNickname($startNickname);
    }

     public function setNickname($value)
     {
         $this->_nickname = $value;
     }

    public function getNickname()
    {
        return $this->_nickname;
    }

    public function setUsername($value)
    {
        if ($value !== null) {
            $this->_params['username'] = $value;
        }
        else {
            unset($this->_params['username']);
        }
    }

    public function getUsername()
    {
        if (array_key_exists('username', $this->_params)) {
            return $this->_params['username'];
        } else {
            return null;
        }
    }

    public function setStartNickname($value)
    {
        if ($value !== null) {
            $this->_params['startNickname'] = $value;
        } else {
            unset($this->_params['startNickname']);
        }
    }

    public function getStartNickname()
    {
        if (array_key_exists('startNickname', $this->_params)) {
            return $this->_params['startNickname'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {
        
        $uri = $this->getBaseUrl();
        $uri .= Zend_Gdata_Gapps::APPS_NICKNAME_PATH;
        if ($this->_nickname !== null) {
            $uri .= '/' . $this->_nickname;
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
