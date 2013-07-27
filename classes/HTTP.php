<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.


class HTTP
{
    // Class constants
    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)';
    const REFERRER = '';
    const TIMEOUT = 30;
    const CONNECT_TIMEOUT = 15;

    private $results = array();

    private $last_error;

    public function __construct()
    {
    }

    public function Get($url, $referrer = null)
    {
        $curl = $this->CurlInit($url, $referrer);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        return $this->CurlExec($curl);
    }

    public function Post($url, $referrer = null, $data = array())
    {
        $curl = $this->CurlInit($url, $referrer);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return $this->CurlExec($curl);
    }

    public function Head($url, $referrer = null)
    {
        $curl = $this->CurlInit($url, $referrer);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        return $this->CurlExec($curl);
    }

    public function __get($key)
    {
        switch($key)
        {
            case 'error':
                return $this->last_error;

            default:
                return $this->results[$key];
        }
    }

    private function CurlInit($url, $referrer)
    {
        $this->results = array('response_header' => '');
        $this->last_error = null;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        //curl_setopt($curl, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(&$this, 'ReadResponseHeader'));
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                                                     'Accept-Language: en-us,en;q=0.5',
                                                     'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                                                     'Keep-Alive: 300',
                                                     'Connection: keep-alive'));

        curl_setopt($curl, CURLOPT_REFERER, empty($referrer) ? self::REFERRER : $referrer);

        return $curl;
    }

    private function CurlExec($curl)
    {
        $response = curl_exec($curl);

        if( $response === false )
        {
            $this->last_error = curl_error($curl);
            curl_close($curl);
            return false;
        }
        else
        {
            $this->results['body'] = $response;
            $this->results = array_merge(curl_getinfo($curl), $this->results);
            curl_close($curl);

            if( $this->results['http_code'] >= 400 )
            {
                if( preg_match('~HTTP/\d\.\d ((\d+).*)~mi', $this->results['response_header'], $matches) )
                {
                    $this->last_error = _T("Text:HTTP Error", $matches[1]);
                }
                else
                {
                    $this->last_error = _T("Text:HTTP Error", $this->results['http_code']);
                }

                return false;
            }

            return true;
        }
    }

    private function ReadResponseHeader($curl, $header)
    {
        $this->results['response_header'] .= $header;
        return strlen($header);
    }
}

?>