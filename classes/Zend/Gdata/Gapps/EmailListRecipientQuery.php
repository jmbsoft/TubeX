<?php


require_once('Zend/Gdata/Gapps/Query.php');

class Zend_Gdata_Gapps_EmailListRecipientQuery extends Zend_Gdata_Gapps_Query
{

    protected $_emailListName = null;

    public function __construct($domain = null, $emailListName = null,
            $startRecipient = null)
    {
        parent::__construct($domain);
        $this->setEmailListName($emailListName);
        $this->setStartRecipient($startRecipient);
    }

     public function setEmailListName($value)
     {
         $this->_emailListName = $value;
     }

    public function getEmailListName()
    {
        return $this->_emailListName;
    }

    public function setStartRecipient($value)
    {
        if ($value !== null) {
            $this->_params['startRecipient'] = $value;
        } else {
            unset($this->_params['startRecipient']);
        }
    }

    public function getStartRecipient()
    {
        if (array_key_exists('startRecipient', $this->_params)) {
            return $this->_params['startRecipient'];
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
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'EmailListName must not be null');
        }
        $uri .= Zend_Gdata_Gapps::APPS_EMAIL_LIST_RECIPIENT_POSTFIX . '/';
        $uri .= $this->getQueryString();
        return $uri;
    }

}
