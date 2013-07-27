<?php


require_once 'Zend/Gdata/App/Util.php';

class Zend_Gdata_Query
{

    protected $_params = array();

    protected $_defaultFeedUri = null;

    protected $_url = null;

    protected $_category = null;

    public function __construct($url = null)
    {
        $this->_url = $url;
    }

    public function getQueryString()
    {
        $queryArray = array();
        foreach ($this->_params as $name => $value) {
            if (substr($name, 0, 1) == '_') {
                continue;
            }
            $queryArray[] = urlencode($name) . '=' . urlencode($value);
        }
        if (count($queryArray) > 0) {
            return '?' . implode('&', $queryArray);
        } else {
            return '';
        }
    }

    public function resetParameters()
    {
        $this->_params = array();
    }

    public function getQueryUrl()
    {
        if ($this->_url == null) {
            $url = $this->_defaultFeedUri;
        } else {
            $url = $this->_url;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url .= $this->getQueryString();
        return $url;
    }

    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    public function getParam($name)
    {
        return $this->_params[$name];
    }

    public function setAlt($value)
    {
        if ($value != null) {
            $this->_params['alt'] = $value;
        } else {
            unset($this->_params['alt']);
        }
        return $this;
    }

    public function setMaxResults($value)
    {
        if ($value != null) {
            $this->_params['max-results'] = $value;
        } else {
            unset($this->_params['max-results']);
        }
        return $this;
    }

    public function setQuery($value)
    {
        if ($value != null) {
            $this->_params['q'] = $value;
        } else {
            unset($this->_params['q']);
        }
        return $this;
    }

    public function setStartIndex($value)
    {
        if ($value != null) {
            $this->_params['start-index'] = $value;
        } else {
            unset($this->_params['start-index']);
        }
        return $this;
    }

    public function setUpdatedMax($value)
    {
        if ($value != null) {
            $this->_params['updated-max'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['updated-max']);
        }
        return $this;
    }

    public function setUpdatedMin($value)
    {
        if ($value != null) {
            $this->_params['updated-min'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['updated-min']);
        }
        return $this;
    }

    public function setPublishedMax($value)
    {
        if ($value !== null) {
            $this->_params['published-max'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['published-max']);
        }
        return $this;
    }

    public function setPublishedMin($value)
    {
        if ($value != null) {
            $this->_params['published-min'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['published-min']);
        }
        return $this;
    }

    public function setAuthor($value)
    {
        if ($value != null) {
            $this->_params['author'] = $value;
        } else {
            unset($this->_params['author']);
        }
        return $this;
    }

    public function getAlt()
    {
        if (array_key_exists('alt', $this->_params)) {
            return $this->_params['alt'];
        } else {
            return null;
        }
    }

    public function getMaxResults()
    {
        if (array_key_exists('max-results', $this->_params)) {
            return intval($this->_params['max-results']);
        } else {
            return null;
        }
    }

    public function getQuery()
    {
        if (array_key_exists('q', $this->_params)) {
            return $this->_params['q'];
        } else {
            return null;
        }
    }

    public function getStartIndex()
    {
        if (array_key_exists('start-index', $this->_params)) {
            return intval($this->_params['start-index']);
        } else {
            return null;
        }
    }

    public function getUpdatedMax()
    {
        if (array_key_exists('updated-max', $this->_params)) {
            return $this->_params['updated-max'];
        } else {
            return null;
        }
    }

    public function getUpdatedMin()
    {
        if (array_key_exists('updated-min', $this->_params)) {
            return $this->_params['updated-min'];
        } else {
            return null;
        }
    }

    public function getPublishedMax()
    {
        if (array_key_exists('published-max', $this->_params)) {
            return $this->_params['published-max'];
        } else {
            return null;
        }
    }

    public function getPublishedMin()
    {
        if (array_key_exists('published-min', $this->_params)) {
            return $this->_params['published-min'];
        } else {
            return null;
        }
    }

    public function getAuthor()
    {
        if (array_key_exists('author', $this->_params)) {
            return $this->_params['author'];
        } else {
            return null;
        }
    }

    public function setCategory($value)
    {
        $this->_category = $value;
        return $this;
    }

    public function getCategory()
    {
        return $this->_category;
    }


    public function __get($name)
    {
        $method = 'get'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method));
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('Property ' . $name . '  does not exist');
        }
    }

    public function __set($name, $val)
    {
        $method = 'set'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method), $val);
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('Property ' . $name . '  does not exist');
        }
    }

}
