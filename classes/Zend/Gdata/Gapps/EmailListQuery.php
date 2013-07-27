<?php


require_once('Zend/Gdata/Gapps/Query.php');

class Zend_Gdata_Gapps_EmailListQuery extends Zend_Gdata_Gapps_Query
{

    protected $_emailListName = null;

    public function __construct($domain = null, $emailListName = null, 
            $recipient = null, $startEmailListName = null)
    {
        parent::__construct($domain);
        $this->setEmailListName($emailListName);
        $this->setRecipient($recipient);
        $this->setStartEmailListName($startEmailListName);
    }

     public function setEmailListName($value)
     {
         $this->_emailListName = $value;
     }

    public function getEmailListName()
    {
        return $this->_emailListName;
    }

    public function setRecipient($value)
    {
        if ($value !== null) {
            $this->_params['recipient'] = $value;
        }
        else {
            unset($this->_params['recipient']);
        }
    }

    public function getRecipient()
    {
        if (array_key_exists('recipient', $this->_params)) {
            return $this->_params['recipient'];
        } else {
            return null;
        }
    }

    public function setStartEmailListName($value)
    {
        if ($value !== null) {
            $this->_params['startEmailListName'] = $value;
        } else {
            unset($this->_params['startEmailListName']);
        }
    }

    public function getStartEmailListName()
    {
        if (array_key_exists('startEmailListName', $this->_params)) {
            return $this->_params['startEmailListName'];
        } else {
            return null;
        }
    }

    public function getQueryUrl()
    {
        
        $uri = $this->getBaseUrl();
        $uri .= Zend_Gdata_Gapps::APPS_EMAIL_LIST_PATH;
        if ($this->_emailListName !== null) {
            $uri .= '/' . $this->_emailListName;
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
