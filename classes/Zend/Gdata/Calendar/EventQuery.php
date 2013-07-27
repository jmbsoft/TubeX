<?php


require_once('Zend/Gdata/App/Util.php');

require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Calendar_EventQuery extends Zend_Gdata_Query
{

    const CALENDAR_FEED_URI = 'http://www.google.com/calendar/feeds';

    protected $_defaultFeedUri = self::CALENDAR_FEED_URI;
    protected $_comments = null;
    protected $_user = null;
    protected $_visibility = null;
    protected $_projection = null;
    protected $_event = null;

    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    public function setComments($value)
    {
        $this->_comments = $value;
        return $this;
    }

    public function setEvent($value)
    {
        $this->_event = $value;
        return $this;
    }

    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    public function setUser($value)
    {
        $this->_user = $value;
        return $this;
    }

    public function setVisibility($value)
    {
        $this->_visibility = $value;
        return $this;
    }

    public function getComments()
    {
        return $this->_comments;
    }

    public function getEvent()
    {
        return $this->_event;
    }

    public function getProjection()
    {
        return $this->_projection;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getVisibility()
    {
        return $this->_visibility;
    }

    public function setStartMax($value)
    {
        if ($value != null) {
            $this->_params['start-max'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['start-max']);
        }
        return $this;
    }

    public function setStartMin($value)
    {
        if ($value != null) {
            $this->_params['start-min'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['start-min']);
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

    public function getStartMax()
    {
        if (array_key_exists('start-max', $this->_params)) {
            return $this->_params['start-max'];
        } else {
            return null;
        }
    }

    public function getStartMin()
    {
        if (array_key_exists('start-min', $this->_params)) {
            return $this->_params['start-min'];
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

    public function getSortOrder()
    {
        if (array_key_exists('sortorder', $this->_params)) {
            return $this->_params['sortorder'];
        } else {
            return null;
        }
    }

    public function setSortOrder($value)
    {
        if ($value != null) {
            $this->_params['sortorder'] = $value;
        } else {
            unset($this->_params['sortorder']);
        }
        return $this;
    }

    public function getRecurrenceExpansionStart()
    {
        if (array_key_exists('recurrence-expansion-start', $this->_params)) {
            return $this->_params['recurrence-expansion-start'];
        } else {
            return null;
        }
    }

    public function setRecurrenceExpansionStart($value)
    {
        if ($value != null) {
            $this->_params['recurrence-expansion-start'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['recurrence-expansion-start']);
        }
        return $this;
    }

    public function getRecurrenceExpansionEnd()
    {
        if (array_key_exists('recurrence-expansion-end', $this->_params)) {
            return $this->_params['recurrence-expansion-end'];
        } else {
            return null;
        }
    }

    public function setRecurrenceExpansionEnd($value)
    {
        if ($value != null) {
            $this->_params['recurrence-expansion-end'] = Zend_Gdata_App_Util::formatTimestamp($value);
        } else {
            unset($this->_params['recurrence-expansion-end']);
        }
        return $this;
    }

    public function getSingleEvents()
    {
        if (array_key_exists('singleevents', $this->_params)) {
            $value = $this->_params['singleevents'];
            switch ($value) {
                case 'true':
                    return true;
                    break;
                case 'false':
                    return false;
                    break;
                default:
                    require_once 'Zend/Gdata/App/Exception.php';
                    throw new Zend_Gdata_App_Exception(
                            'Invalid query param value for futureevents: ' .
                            $value . ' It must be a boolean.');
            }
        } else {
            return null;
        }
    }

    public function setSingleEvents($value)
    {
        if (!is_null($value)) {
            if (is_bool($value)) {
                $this->_params['singleevents'] = ($value?'true':'false');
            } elseif ($value == 'true' | $value == 'false') {
                $this->_params['singleevents'] = $value;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                        'Invalid query param value for futureevents: ' .
                        $value . ' It must be a boolean.');
            }
        } else {
            unset($this->_params['singleevents']);
        }
        return $this;
    }

    public function getFutureEvents()
    {
        if (array_key_exists('futureevents', $this->_params)) {
            $value = $this->_params['futureevents'];
            switch ($value) {
                case 'true':
                    return true;
                    break;
                case 'false':
                    return false;
                    break;
                default:
                    require_once 'Zend/Gdata/App/Exception.php';
                    throw new Zend_Gdata_App_Exception(
                            'Invalid query param value for futureevents: ' .
                            $value . ' It must be a boolean.');
            }
        } else {
            return null;
        }
    }

    public function setFutureEvents($value)
    {
        if (!is_null($value)) {
            if (is_bool($value)) {
                $this->_params['futureevents'] = ($value?'true':'false');
            } elseif ($value == 'true' | $value == 'false') {
                $this->_params['futureevents'] = $value;
            } else {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                        'Invalid query param value for futureevents: ' .
                        $value . ' It must be a boolean.');
            }
        } else {
            unset($this->_params['futureevents']);
        }
        return $this;
    }

    public function getQueryUrl()
    {
        if (isset($this->_url)) {
            $uri = $this->_url;
        } else {
            $uri = $this->_defaultFeedUri;
        }
        if ($this->getUser() != null) {
            $uri .= '/' . $this->getUser();
        } else {
            $uri .= '/default';
        }
        if ($this->getVisibility() != null) {
            $uri .= '/' . $this->getVisibility();
        } else {
            $uri .= '/public';
        }
        if ($this->getProjection() != null) {
            $uri .= '/' . $this->getProjection();
        } else {
            $uri .= '/full';
        }
        if ($this->getEvent() != null) {
            $uri .= '/' . $this->getEvent();
            if ($this->getComments() != null) {
                $uri .= '/comments/' . $this->getComments();
            }
        }
        $uri .= $this->getQueryString();
        return $uri;
    }

}
