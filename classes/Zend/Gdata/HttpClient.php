<?php


require_once 'Zend/Exception.php';

require_once 'Zend/Http/Client.php';

class Zend_Gdata_HttpClient extends Zend_Http_Client
{

    private $_authSubPrivateKeyId = null;

    private $_authSubToken = null;

    private $_clientLoginToken = null;

    private $_clientLoginKey = null;

    protected $_streamingRequest = null;

    public function setAuthSubPrivateKeyFile($file, $passphrase = null, 
                                             $useIncludePath = false) {
        $fp = fopen($file, "r", $useIncludePath);
        $key = '';
        while (!feof($fp)) {
            $key .= fread($fp, 8192);
        }
        $this->setAuthSubPrivateKey($key, $passphrase);
        fclose($fp);
    }

    public function setAuthSubPrivateKey($key, $passphrase = null) {
        if ($key != null && !function_exists('openssl_pkey_get_private')) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You cannot enable secure AuthSub if the openssl module ' .
                    'is not enabled in your PHP installation.');
        }
        $this->_authSubPrivateKeyId = openssl_pkey_get_private(
                $key, $passphrase);
        return $this;
    }

    public function getAuthSubPrivateKeyId() {
        return $this->_authSubPrivateKeyId;
    }

    public function getAuthSubToken() {
        return $this->_authSubToken;
    }

    public function setAuthSubToken($token) {
        $this->_authSubToken = $token;
        return $this;
    }

    public function getClientLoginToken() {
        return $this->_clientLoginToken;
    }

    public function setClientLoginToken($token) {
        $this->_clientLoginToken = $token;
        return $this;
    }

    public function filterHttpRequest($method, $url, $headers = array(), $body = null, $contentType = null) {
        if ($this->getAuthSubToken() != null) {
            // AuthSub authentication
            if ($this->getAuthSubPrivateKeyId() != null) {
                // secure AuthSub
                $time = time();
                $nonce = mt_rand(0, 999999999);
                $dataToSign = $method . ' ' . $url . ' ' . $time . ' ' . $nonce;

                // compute signature
                $pKeyId = $this->getAuthSubPrivateKeyId();
                $signSuccess = openssl_sign($dataToSign, $signature, $pKeyId, 
                                            OPENSSL_ALGO_SHA1);
                if (!$signSuccess) {
                    require_once 'Zend/Gdata/App/Exception.php';
                    throw new Zend_Gdata_App_Exception(
                            'openssl_signing failure - returned false');
                }
                // encode signature
                $encodedSignature = base64_encode($signature);

                // final header
                $headers['authorization'] = 'AuthSub token="' . $this->getAuthSubToken() . '" ' .
                                            'data="' . $dataToSign . '" ' .
                                            'sig="' . $encodedSignature . '" ' .
                                            'sigalg="rsa-sha1"';
            } else {
                // AuthSub without secure tokens
                $headers['authorization'] = 'AuthSub token="' . $this->getAuthSubToken() . '"';
            }
        } elseif ($this->getClientLoginToken() != null) {
            $headers['authorization'] = 'GoogleLogin auth=' . $this->getClientLoginToken();
        }
        return array('method' => $method, 'url' => $url, 'body' => $body, 'headers' => $headers, 'contentType' => $contentType);
    }

    public function filterHttpResponse($response) {
        return $response;
    }

    public function getAdapter()
    {
    	return $this->adapter;
    }

    public function setAdapter($adapter)
    {
        if ($adapter == null) {
            $this->adapter = $adapter;
        } else {
        	  parent::setAdapter($adapter);
        }
    }

    public function setStreamingRequest($value)
    {
        $this->_streamingRequest = $value;
    }

    public function getStreamingRequest()
    {
        if ($this->_streamingRequest()) {
            return true;
        } else {
            return false;
        }
    }

    protected function _prepareBody()
    {
        if($this->_streamingRequest) {
            $this->setHeaders(self::CONTENT_LENGTH,
                $this->raw_post_data->getTotalSize());
            return $this->raw_post_data;
        }
        else {
            return parent::_prepareBody();
        }
    }

    public function resetParameters()
    {
        $this->_streamingRequest = false;

        return parent::resetParameters();
    }

    public function setRawDataStream($data, $enctype = null)
    {
        $this->_streamingRequest = true;
        return $this->setRawData($data, $enctype);
    }

}
