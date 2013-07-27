<?php


require_once 'Zend/Gdata/App/MediaSource.php';

abstract class Zend_Gdata_App_BaseMediaSource implements Zend_Gdata_App_MediaSource
{

    protected $_contentType = null;

    protected $_slug = null;

    public function getContentType()
    {
        return $this->_contentType;
    }

    public function setContentType($value)
    {
        $this->_contentType = $value;
        return $this;
    }

    public function getSlug(){
        return $this->_slug;
    }

    public function setSlug($value){
        $this->_slug = $value;
        return $this;
    }

    public function __get($name)
    {
        $method = 'get'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method));
        } else if (property_exists($this, "_${name}")) {
            return $this->{'_' . $name};
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . ' does not exist');
        }
    }

    public function __set($name, $val)
    {
        $method = 'set'.ucfirst($name);
        if (method_exists($this, $method)) {
            return call_user_func(array(&$this, $method), $val);
        } else if (isset($this->{'_' . $name}) || is_null($this->{'_' . $name})) {
            $this->{'_' . $name} = $val;
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . '  does not exist');
        }
    }

    public function __isset($name)
    {
        $rc = new ReflectionClass(get_class($this));
        $privName = '_' . $name;
        if (!($rc->hasProperty($privName))) {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Property ' . $name . ' does not exist');
        } else {
            if (isset($this->{$privName})) {
                if (is_array($this->{$privName})) {
                    if (count($this->{$privName}) > 0) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
    }
    
}
