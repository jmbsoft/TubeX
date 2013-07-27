<?php


require_once 'Zend/Uri/Http.php';
require_once 'Zend/Http/Response.php';
require_once 'Zend/Http/Client/Adapter/Interface.php';

class Zend_Http_Client_Adapter_Test implements Zend_Http_Client_Adapter_Interface
{

    protected $config = array();

    protected $responses = array("HTTP/1.1 400 Bad Request\r\n\r\n");

    protected $responseIndex = 0;

    public function __construct()
    { }

    public function setConfig($config = array())
    {
        if (! is_array($config)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                '$config expects an array, ' . gettype($config) . ' recieved.');
        }

        foreach ($config as $k => $v) {
            $this->config[strtolower($k)] = $v;
        }
    }

    public function connect($host, $port = 80, $secure = false)
    { }

    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        $host = $uri->getHost();
            $host = (strtolower($uri->getScheme()) == 'https' ? 'sslv2://' . $host : $host);

        // Build request headers
        $path = $uri->getPath();
        if ($uri->getQuery()) $path .= '?' . $uri->getQuery();
        $request = "{$method} {$path} HTTP/{$http_ver}\r\n";
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = ucfirst($k) . ": $v";
            $request .= "$v\r\n";
        }

        // Add the request body
        $request .= "\r\n" . $body;

        // Do nothing - just return the request as string

        return $request;
    }

    public function read()
    {
        if ($this->responseIndex >= count($this->responses)) {
            $this->responseIndex = 0;
        }
        return $this->responses[$this->responseIndex++];
    }

    public function close()
    { }

    public function setResponse($response)
    {
        if ($response instanceof Zend_Http_Response) {
            $response = $response->asString();
        }

        $this->responses = (array)$response;
        $this->responseIndex = 0;
    }

    public function addResponse($response)
    {
        $this->responses[] = $response;
    }

    public function setResponseIndex($index)
    {
        if ($index < 0 || $index >= count($this->responses)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Index out of range of response buffer size');
        }
        $this->responseIndex = $index;
    }
}
