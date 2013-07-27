<?php


require_once 'Zend/Gdata/App/Exception.php';

require_once 'Zend/Http/Client/Exception.php';

class Zend_Gdata_App_HttpException extends Zend_Gdata_App_Exception
{

    protected $_httpClientException = null;
    protected $_response = null;

    public function __construct($message = null, $e = null, $response = null)
    {
        $this->_httpClientException = $e;
        $this->_response = $response;
        parent::__construct($message);
    }

    public function getHttpClientException()
    {
        return $this->_httpClientException;
    }

    public function setHttpClientException($value)
    {
        $this->_httpClientException = $value;
        return $this;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getRawResponseBody()
    {
        if ($this->getResponse()) {
            $response = $this->getResponse();
            return $response->getRawBody();
        }
        return null;
    }

}
