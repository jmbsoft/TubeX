<?php


require_once 'Zend/Gdata/HttpClient.php';

require_once 'Zend/Version.php';

class Zend_Gdata_AuthSub
{

    const AUTHSUB_REQUEST_URI      = 'https://www.google.com/accounts/AuthSubRequest';

    const AUTHSUB_SESSION_TOKEN_URI = 'https://www.google.com/accounts/AuthSubSessionToken';

    const AUTHSUB_REVOKE_TOKEN_URI  = 'https://www.google.com/accounts/AuthSubRevokeToken';

    const AUTHSUB_TOKEN_INFO_URI    = 'https://www.google.com/accounts/AuthSubTokenInfo';

     public static function getAuthSubTokenUri($next, $scope, $secure=0, $session=0, 
                                               $request_uri = self::AUTHSUB_REQUEST_URI)
     {
         $querystring = '?next=' . urlencode($next)
             . '&scope=' . urldecode($scope)
             . '&secure=' . urlencode($secure)
             . '&session=' . urlencode($session);
         return $request_uri . $querystring;
     }

    public static function getAuthSubSessionToken(
            $token, $client = null, 
            $request_uri = self::AUTHSUB_SESSION_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);
   
        if ($client instanceof Zend_Gdata_HttpClient) { 
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }

        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }

        // Parse Google's response
        if ($response->isSuccessful()) {
            $goog_resp = array();
            foreach (explode("\n", $response->getBody()) as $l) {
                $l = chop($l);
                if ($l) {
                    list($key, $val) = explode('=', chop($l), 2);
                    $goog_resp[$key] = $val;
                }
            }
            return $goog_resp['Token'];
        } else {
            require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                    'Token upgrade failed. Reason: ' . $response->getBody());
        }
    }

    public static function AuthSubRevokeToken($token, $client = null,
                                              $request_uri = self::AUTHSUB_REVOKE_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);
 
        if ($client instanceof Zend_Gdata_HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
            $client->resetParameters();
        } else {
            $client->setUri($request_uri);
        }

        ob_start();
        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        // Parse Google's response
        if ($response->isSuccessful()) {
            return true;
        } else {
            return false;
        }
    }

    public static function getAuthSubTokenInfo(
            $token, $client = null, $request_uri = self::AUTHSUB_TOKEN_INFO_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof Zend_Gdata_HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }

        ob_start();
        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        return $response->getBody();
    }

    public static function getHttpClient($token, $client = null)
    {
        if ($client == null) {
            $client = new Zend_Gdata_HttpClient();
        }
        if (!$client instanceof Zend_Http_Client) {
            require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException('Client is not an instance of Zend_Http_Client.');
        }
        $useragent = 'Zend_Framework_Gdata/' . Zend_Version::VERSION;
        $client->setConfig(array(
                'strictredirects' => true,
                'useragent' => $useragent
            )
        );
        $client->setAuthSubToken($token);
        return $client;
    }

}
