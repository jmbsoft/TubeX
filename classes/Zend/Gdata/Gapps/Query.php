<?php


require_once('Zend/Gdata/Query.php');

require_once('Zend/Gdata/Gapps.php');

abstract class Zend_Gdata_Gapps_Query extends Zend_Gdata_Query
{

    protected $_domain = null;

    public function __construct($domain = null)
    {
        parent::__construct();
        $this->_domain = $domain;
    }

    public function setDomain($value)
    {
        $this->_domain = $value;
    }

    public function getDomain()
    {
        return $this->_domain;
    }

     public function getBaseUrl($domain = null)
     {
         if ($domain !== null) {
             return Zend_Gdata_Gapps::APPS_BASE_FEED_URI . '/' . $domain;
         }
         else if ($this->_domain !== null) {
             return Zend_Gdata_Gapps::APPS_BASE_FEED_URI . '/' . $this->_domain;
         }
         else {
             require_once 'Zend/Gdata/App/InvalidArgumentException.php';
             throw new Zend_Gdata_App_InvalidArgumentException(
                 'Domain must be specified.');
         }
     }

}
