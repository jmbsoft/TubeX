<?php


require_once 'Zend/Gdata/App/AuthException.php';

class Zend_Gdata_App_CaptchaRequiredException extends Zend_Gdata_App_AuthException
{

    const ACCOUNTS_URL = 'https://www.google.com/accounts/';

    private $captchaToken;

    private $captchaUrl;

    public function __construct($captchaToken, $captchaUrl) {
        $this->captchaToken = $captchaToken;
        $this->captchaUrl = Zend_Gdata_App_CaptchaRequiredException::ACCOUNTS_URL . $captchaUrl;
        parent::__construct('CAPTCHA challenge issued by server');
    }

    public function getCaptchaToken() {
        return $this->captchaToken;
    }

    public function getCaptchaUrl() {
        return $this->captchaUrl;
    }
    
}
