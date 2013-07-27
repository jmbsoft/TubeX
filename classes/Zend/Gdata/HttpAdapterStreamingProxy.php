<?php


require_once 'Zend/Http/Client/Adapter/Proxy.php';

class Zend_Gdata_HttpAdapterStreamingProxy extends Zend_Http_Client_Adapter_Proxy
{

    const CHUNK_SIZE = 1024;

    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        // If no proxy is set, throw an error
        if (! $this->config['proxy_host']) {
        	require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception('No proxy host set!');
        }

        // Make sure we're properly connected
        if (! $this->socket) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Trying to write but we are not connected');
        }

        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        if ($this->connected_to[0] != $host || $this->connected_to[1] != $port) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Trying to write but we are connected to the wrong proxy ' .
                'server');
        }

        // Add Proxy-Authorization header
        if ($this->config['proxy_user'] && ! isset($headers['proxy-authorization'])) {
            $headers['proxy-authorization'] = Zend_Http_Client::encodeAuthHeader(
                $this->config['proxy_user'], $this->config['proxy_pass'], $this->config['proxy_auth']
            );
        }
                
        // if we are proxying HTTPS, preform CONNECT handshake with the proxy
        if ($uri->getScheme() == 'https' && (! $this->negotiated)) {
            $this->connectHandshake($uri->getHost(), $uri->getPort(), $http_ver, $headers);
            $this->negotiated = true;
        }
        
        // Save request method for later
        $this->method = $method;

        // Build request headers
        $request = "{$method} {$uri->__toString()} HTTP/{$http_ver}\r\n";

        // Add all headers to the request string
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = "$k: $v";
            $request .= "$v\r\n";
        }

        $request .= "\r\n";

        // Send the request headers
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Error writing request to proxy server');
        }

        //read from $body, write to socket
        while ($body->hasData()) {
            if (! @fwrite($this->socket, $body->read(self::CHUNK_SIZE))) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception(
                    'Error writing request to server');
            }
        }
        return 'Large upload, request is not cached.';
    }
}
