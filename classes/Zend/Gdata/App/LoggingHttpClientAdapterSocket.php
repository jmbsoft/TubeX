<?php


require_once 'Zend/Http/Client/Adapter/Socket.php';

class Zend_Gdata_App_LoggingHttpClientAdapterSocket extends Zend_Http_Client_Adapter_Socket
{

    protected $log_handle = null;

    protected function log($message)
    {
        if ($this->log_handle == null) {
            $this->log_handle = fopen($this->config['logfile'], 'a');
        }
        fwrite($this->log_handle, $message);
    }

    public function connect($host, $port = 80, $secure = false)
    {
        $this->log("Connecting to: ${host}:${port}");
        return parent::connect($host, $port, $secure);
    }

    public function write($method, $uri, $http_ver = '1.1', $headers = array(), $body = '')
    {
        $request = parent::write($method, $uri, $http_ver, $headers, $body);
        $this->log("\n\n" . $request);
        return $request;
    }

    public function read()
    {
        $response = parent::read();
        $this->log("${response}\n\n");
        return $response;
    }

    public function close()
    {
        $this->log("Closing socket\n\n");
        parent::close();
    }

}
