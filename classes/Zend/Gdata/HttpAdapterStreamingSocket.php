<?php


require_once 'Zend/Http/Client/Adapter/Socket.php';

class Zend_Gdata_HttpAdapterStreamingSocket extends Zend_Http_Client_Adapter_Socket
{

    const CHUNK_SIZE = 1024;

    public function write($method, $uri, $http_ver = '1.1', $headers = array(),
        $body = '')
    {
        // Make sure we're properly connected
        if (! $this->socket) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Trying to write but we are not connected');
        }

        $host = $uri->getHost();
        $host = (strtolower($uri->getScheme()) == 'https' ? $this->config['ssltransport'] : 'tcp') . '://' . $host;
        if ($this->connected_to[0] != $host || $this->connected_to[1] != $uri->getPort()) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Trying to write but we are connected to the wrong host');
        }

        // Save request method for later
        $this->method = $method;

        // Build request headers
        $path = $uri->getPath();
        if ($uri->getQuery()) $path .= '?' . $uri->getQuery();
        $request = "{$method} {$path} HTTP/{$http_ver}\r\n";
        foreach ($headers as $k => $v) {
            if (is_string($k)) $v = ucfirst($k) . ": $v";
            $request .= "$v\r\n";
        }

        // Send the headers over
        $request .= "\r\n";
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception(
                'Error writing request to server');
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
