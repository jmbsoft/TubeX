<?php


require_once 'Zend/Uri/Http.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Socket.php';

class Zend_Http_Client_Adapter_Proxy extends Zend_Http_Client_Adapter_Socket
{

    protected $config = array(
        'ssltransport'  => 'ssl',
        'proxy_host'    => '',
        'proxy_port'    => 8080,
        'proxy_user'    => '',
        'proxy_pass'    => '',
        'proxy_auth'    => Zend_Http_Client::AUTH_BASIC,
        'persistent'    => false
    );

    protected $negotiated = false;

    public function connect($host, $port = 80, $secure = false)
    {
        // If no proxy is set, fall back to Socket adapter
        if (! $this->config['proxy_host']) return parent::connect($host, $port, $secure);

        // Go through a proxy - the connection is actually to the proxy server
        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        // If we are connected to the wrong proxy, disconnect first
        if (($this->connected_to[0] != $host || $this->connected_to[1] != $port)) {
            if (is_resource($this->socket)) $this->close();
        }

        // Now, if we are not connected, connect
        if (! is_resource($this->socket) || ! $this->config['keepalive']) {
            $this->socket = @fsockopen($host, $port, $errno, $errstr, (int) $this->config['timeout']);
            if (! $this->socket) {
                $this->close();
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception(
                    'Unable to Connect to proxy server ' . $host . ':' . $port . '. Error #' . $errno . ': ' . $errstr);
            }

            // Set the stream timeout
            if (!stream_set_timeout($this->socket, (int) $this->config['timeout'])) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception('Unable to set the connection timeout');
            }

            // Update connected_to
            $this->connected_to = array($host, $port);
        }
    }

    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        // If no proxy is set, fall back to default Socket adapter
        if (! $this->config['proxy_host']) return parent::write($method, $uri, $http_ver, $headers, $body);

        // Make sure we're properly connected
        if (! $this->socket) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are not connected");
        }

        $host = $this->config['proxy_host'];
        $port = $this->config['proxy_port'];

        if ($this->connected_to[0] != $host || $this->connected_to[1] != $port) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Trying to write but we are connected to the wrong proxy server");
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

        // Add the request body
        $request .= "\r\n" . $body;

        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Error writing request to proxy server");
        }

        return $request;
    }

    protected function connectHandshake($host, $port = 443, $http_ver = '1.1', array &$headers = array())
    {
        $request = "CONNECT $host:$port HTTP/$http_ver\r\n" . 
                   "Host: " . $this->config['proxy_host'] . "\r\n";

        // Add the user-agent header
        if (isset($this->config['useragent'])) {
            $request .= "User-agent: " . $this->config['useragent'] . "\r\n";
        }
        
        // If the proxy-authorization header is set, send it to proxy but remove
        // it from headers sent to target host
        if (isset($headers['proxy-authorization'])) {
            $request .= "Proxy-authorization: " . $headers['proxy-authorization'] . "\r\n";
            unset($headers['proxy-authorization']);
        }
    
        $request .= "\r\n";

        // Send the request
        if (! @fwrite($this->socket, $request)) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Error writing request to proxy server");
        }

        // Read response headers only
        $response = '';
        $gotStatus = false;
        while ($line = @fgets($this->socket)) {
            $gotStatus = $gotStatus || (strpos($line, 'HTTP') !== false);
            if ($gotStatus) {
                $response .= $line;
                if (!chop($line)) break;
            }
        }
        
        // Check that the response from the proxy is 200
        if (Zend_Http_Response::extractCode($response) != 200) {
            require_once 'Zend/Http/Client/Adapter/Exception.php';
            throw new Zend_Http_Client_Adapter_Exception("Unable to connect to HTTPS proxy. Server response: " . $response);
        }
        
        // If all is good, switch socket to secure mode. We have to fall back
        // through the different modes 
        $modes = array(
            STREAM_CRYPTO_METHOD_TLS_CLIENT, 
            STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv23_CLIENT,
            STREAM_CRYPTO_METHOD_SSLv2_CLIENT 
        );
        
        $success = false; 
        foreach($modes as $mode) {
            $success = stream_socket_enable_crypto($this->socket, true, $mode);
            if ($success) break;
        }
        
        if (! $success) {
                require_once 'Zend/Http/Client/Adapter/Exception.php';
                throw new Zend_Http_Client_Adapter_Exception("Unable to connect to" . 
                    " HTTPS server through proxy: could not negotiate secure connection.");
        }
    }

    public function close()
    {
        parent::close();
        $this->negotiated = false;
    }

    public function __destruct()
    {
        if ($this->socket) $this->close();
    }
}
