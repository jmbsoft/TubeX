<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Gapps.php';

class Zend_Gdata_Gapps_Extension_Login extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'apps';
    protected $_rootElement = 'login';

    protected $_username = null;

    protected $_password = null;

    protected $_hashFunctionName = null;

    protected $_admin = null;

    protected $_agreedToTerms = null;

    protected $_suspended = null;

    protected $_changePasswordAtNextLogin = null;

    public function __construct($username = null, $password = null,
        $hashFunctionName = null, $admin = null, $suspended = null,
        $changePasswordAtNextLogin = null, $agreedToTerms = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct();
        $this->_username = $username;
        $this->_password = $password;
        $this->_hashFunctionName = $hashFunctionName;
        $this->_admin = $admin;
        $this->_agreedToTerms = $agreedToTerms;
        $this->_suspended = $suspended;
        $this->_changePasswordAtNextLogin = $changePasswordAtNextLogin;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_username !== null) {
            $element->setAttribute('userName', $this->_username);
        }
        if ($this->_password !== null) {
            $element->setAttribute('password', $this->_password);
        }
        if ($this->_hashFunctionName !== null) {
            $element->setAttribute('hashFunctionName', $this->_hashFunctionName);
        }
        if ($this->_admin !== null) {
            $element->setAttribute('admin', ($this->_admin ? "true" : "false"));
        }
        if ($this->_agreedToTerms !== null) {
            $element->setAttribute('agreedToTerms', ($this->_agreedToTerms ? "true" : "false"));
        }
        if ($this->_suspended !== null) {
            $element->setAttribute('suspended', ($this->_suspended ? "true" : "false"));
        }
        if ($this->_changePasswordAtNextLogin !== null) {
            $element->setAttribute('changePasswordAtNextLogin', ($this->_changePasswordAtNextLogin ? "true" : "false"));
        }

        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'userName':
            $this->_username = $attribute->nodeValue;
            break;
        case 'password':
            $this->_password = $attribute->nodeValue;
            break;
        case 'hashFunctionName':
            $this->_hashFunctionName = $attribute->nodeValue;
            break;
        case 'admin':
            if ($attribute->nodeValue == "true") {
                $this->_admin = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_admin = false;
            }
            else {
                require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#admin.");
            }
            break;
        case 'agreedToTerms':
            if ($attribute->nodeValue == "true") {
                $this->_agreedToTerms = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_agreedToTerms = false;
            }
            else {
                require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#agreedToTerms.");
            }
            break;
        case 'suspended':
            if ($attribute->nodeValue == "true") {
                $this->_suspended = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_suspended = false;
            }
            else {
                require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#suspended.");
            }
            break;
        case 'changePasswordAtNextLogin':
            if ($attribute->nodeValue == "true") {
                $this->_changePasswordAtNextLogin = true;
            }
            else if ($attribute->nodeValue == "false") {
                $this->_changePasswordAtNextLogin = false;
            }
            else {
                require_once('Zend/Gdata/App/InvalidArgumentException.php');
                throw new Zend_Gdata_App_InvalidArgumentException("Expected 'true' or 'false' for apps:login#changePasswordAtNextLogin.");
            }
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function setUsername($value)
    {
        $this->_username = $value;
        return $this;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($value)
    {
        $this->_password = $value;
        return $this;
    }

    public function getHashFunctionName()
    {
        return $this->_hashFunctionName;
    }

    public function setHashFunctionName($value)
    {
        $this->_hashFunctionName = $value;
        return $this;
    }

    public function getAdmin()
    {
        if (!(is_bool($this->_admin))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for admin.');
        }
        return $this->_admin;
    }

    public function setAdmin($value)
    {
        if (!(is_bool($value))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_admin = $value;
        return $this;
    }

    public function getAgreedToTerms()
    {
        if (!(is_bool($this->_agreedToTerms))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for agreedToTerms.');
        }
        return $this->_agreedToTerms;
    }

    public function setAgreedToTerms($value)
    {
        if (!(is_bool($value))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_agreedToTerms = $value;
        return $this;
    }

    public function getSuspended()
    {
        if (!(is_bool($this->_suspended))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for suspended.');
        }
        return $this->_suspended;
    }

    public function setSuspended($value)
    {
        if (!(is_bool($value))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_suspended = $value;
        return $this;
    }

    public function getChangePasswordAtNextLogin()
    {
        if (!(is_bool($this->_changePasswordAtNextLogin))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for changePasswordAtNextLogin.');
        }
        return $this->_changePasswordAtNextLogin;
    }

    public function setChangePasswordAtNextLogin($value)
    {
        if (!(is_bool($value))) {
            require_once('Zend/Gdata/App/InvalidArgumentException.php');
            throw new Zend_Gdata_App_InvalidArgumentException('Expected boolean for $value.');
        }
        $this->_changePasswordAtNextLogin = $value;
        return $this;
    }

    public function __toString()
    {
        return "Username: " . $this->getUsername() .
            "\nPassword: " . (is_null($this->getPassword()) ? "NOT SET" : "SET") .
            "\nPassword Hash Function: " . $this->getHashFunctionName() .
            "\nAdministrator: " . ($this->getAdmin() ? "Yes" : "No") .
            "\nAgreed To Terms: " . ($this->getAgreedToTerms() ? "Yes" : "No") .
            "\nSuspended: " . ($this->getSuspended() ? "Yes" : "No");
    }
}
