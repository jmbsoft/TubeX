<?php


interface Zend_Http_Client_Adapter_Interface
{

    public function setConfig($config = array());

    public function connect($host, $port = 80, $secure = false);

    public function write($method, $url, $http_ver = '1.1', $headers = array(), $body = '');

    public function read();

    public function close();
}
