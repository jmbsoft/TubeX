<?php


require_once('Zend/Gdata/YouTube.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_YouTube_VideoQuery extends Zend_Gdata_Query
{

    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    public function setFeedType($feedType, $videoId = null, $entry = null)
    {
        switch ($feedType) {
        case 'top rated':
            $this->_url = Zend_Gdata_YouTube::STANDARD_TOP_RATED_URI;
            break;
        case 'most viewed':
            $this->_url = Zend_Gdata_YouTube::STANDARD_MOST_VIEWED_URI;
            break;
        case 'recently featured':
            $this->_url = Zend_Gdata_YouTube::STANDARD_RECENTLY_FEATURED_URI;
            break;
        case 'mobile':
            $this->_url = Zend_Gdata_YouTube::STANDARD_WATCH_ON_MOBILE_URI;
            break;
        case 'related':
            if ($videoId === null) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' . $videoId .
                    '/related';
            }
            break;
        case 'responses':
            if ($videoId === null) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_Exception(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' . $videoId .
                    'responses';
            }
            break;
        case 'comments':
            if ($videoId === null) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_Exception(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' .
                    $videoId . 'comments';
                if ($entry !== null) {
                    $this->_url .= '/' . $entry;
                }
            }
            break;
        default:
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('Unknown feed type');
            break;
        }
    }

    public function setLocation($value)
    {
        switch($value) {
            case null:
                unset($this->_params['location']);
            default:
                $parameters = explode(',', $value);
                if (count($parameters) != 2) {
                    require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                    throw new Zend_Gdata_App_InvalidArgumentException(
                        'You must provide 2 coordinates to the location ' .
                        'URL parameter');
                }

                foreach($parameters as $param) {
                    $temp = trim($param);
                    // strip off the optional exclamation mark for numeric check
                    if (substr($temp, -1) == '!') {
                        $temp = substr($temp, -1);
                    }
                    if (!is_numeric($temp)) {
                        require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                        throw new Zend_Gdata_App_InvalidArgumentException(
                            'Value provided to location parameter must' .
                            ' be in the form of two coordinates');
                    }
                }
                $this->_params['location'] = $value;
        }
    }

    public function getLocation()
    {
        if (array_key_exists('location', $this->_params)) {
            return $this->_params['location'];
        } else {
            return null;
        }
    }

    public function setLocationRadius($value)
    {
        switch($value) {
        	case null:
                unset($this->_params['location-radius']);
            default:
                $this->_params['location-radius'] = $value;
        }
    }

    public function getLocationRadius()
    {
        if (array_key_exists('location-radius', $this->_params)) {
            return $this->_params['location-radius'];
        } else {
            return null;
        }
    }

    public function setTime($value = null)
    {
        switch ($value) {
            case 'today':
                $this->_params['time'] = 'today';
                break;
            case 'this_week':
                $this->_params['time'] = 'this_week';
                break;
            case 'this_month':
                $this->_params['time'] = 'this_month';
                break;
            case 'all_time':
                $this->_params['time'] = 'all_time';
                break;
            case null:
                unset($this->_params['time']);
            default:
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Unknown time value');
                break;
        }
        return $this;
    }

    public function setUploader($value = null)
    {
        switch ($value) {
            case 'partner':
                $this->_params['uploader'] = 'partner';
                break;
            case null:
                unset($this->_params['uploader']);
                break;
            default:
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Unknown value for uploader');
        }
        return $this;
    }

    public function setVideoQuery($value = null)
    {
        if ($value != null) {
            $this->_params['vq'] = $value;
        } else {
            unset($this->_params['vq']);
        }
        return $this;
    }

    public function setFormat($value = null)
    {
        if ($value != null) {
            $this->_params['format'] = $value;
        } else {
            unset($this->_params['format']);
        }
        return $this;
    }

    public function setRacy($value = null)
    {
        switch ($value) {
            case 'include':
                $this->_params['racy'] = $value;
                break;
            case 'exclude':
                $this->_params['racy'] = $value;
                break;
            case null:
                unset($this->_params['racy']);
                break;
        }
        return $this;
    }

    public function getRacy()
    {
        if (array_key_exists('racy', $this->_params)) {
            return $this->_params['racy'];
        } else {
            return null;
        }
    }

    public function setSafeSearch($value)
    {
    	switch ($value) {
            case 'none':
                $this->_params['safeSearch'] = 'none';
                break;
    		case 'moderate':
                $this->_params['safeSearch'] = 'moderate';
                break;
            case 'strict':
                $this->_params['safeSearch'] = 'strict';
                break;
            case null:
                unset($this->_params['safeSearch']);
            default:
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'The safeSearch parameter only supports the values '.
                    '\'none\', \'moderate\' or \'strict\'.');
    	}
    }

    public function getSafeSearch()
    {
    	if (array_key_exists('safeSearch', $this->_params)) {
    		return $this->_params['safeSearch'];
    	}
        return $this;
    }

    public function setOrderBy($value)
    {
        if ($value != null) {
            $this->_params['orderby'] = $value;
        } else {
            unset($this->_params['orderby']);
        }
        return $this;
    }

    public function getFormat()
    {
        if (array_key_exists('format', $this->_params)) {
            return $this->_params['format'];
        } else {
            return null;
        }
    }

    public function getVideoQuery()
    {
        if (array_key_exists('vq', $this->_params)) {
            return $this->_params['vq'];
        } else {
            return null;
        }
    }

    public function getTime()
    {
        if (array_key_exists('time', $this->_params)) {
            return $this->_params['time'];
        } else {
            return null;
        }
    }

    public function getOrderBy()
    {
        if (array_key_exists('orderby', $this->_params)) {
            return $this->_params['orderby'];
        } else {
            return null;
        }
    }

    public function getQueryString($majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        $queryArray = array();

        foreach ($this->_params as $name => $value) {
            if (substr($name, 0, 1) == '_') {
                continue;
            }

            switch($name) {
                case 'location-radius':
                    if ($majorProtocolVersion == 1) {
                        require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2.");
                    }
                    break;

            	case 'racy':
                    if ($majorProtocolVersion == 2) {
                        require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is not supported in version 2. " .
                            "Please use 'safeSearch'.");
                    }
                    break;

                case 'safeSearch':
                    if ($majorProtocolVersion == 1) {
                        require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2. " .
                            "Please use 'racy'.");
                    }
                    break;

                case 'uploader':
                    if ($majorProtocolVersion == 1) {
                        require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2.");
                    }
                    break;

                case 'vq':
                    if ($majorProtocolVersion == 2) {
                        $name = 'q';
                    }
                    break;
            }

            $queryArray[] = urlencode($name) . '=' . urlencode($value);

        }
        if (count($queryArray) > 0) {
            return '?' . implode('&', $queryArray);
        } else {
            return '';
        }
    }

    public function getQueryUrl($majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        if (isset($this->_url)) {
            $url = $this->_url;
        } else {
            $url = Zend_Gdata_YouTube::VIDEO_URI;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url = $url . $this->getQueryString($majorProtocolVersion,
            $minorProtocolVersion);
        return $url;
    }

}
