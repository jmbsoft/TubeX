<?php


require_once('Zend/Gdata/Query.php');

class Zend_Gdata_Health_Query extends Zend_Gdata_Query
{

    const HEALTH_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/default';

    const HEALTH_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/default';

    const ITEM_CATEGORY_NS = 'http://schemas.google.com/health/item';

    protected $_defaultFeedUri = self::HEALTH_PROFILE_FEED_URI;

    public function setDigest($value)
    {
        if ($value !== null) {
            $this->_params['digest'] = $value;
        }
        return $this;
    }

    public function getDigest()
    {
        if (array_key_exists('digest', $this->_params)) {
            return $this->_params['digest'];
        } else {
            return null;
        }
    }

    public function setCategory($item, $name = null)
    {
        $this->_category = $item . 
            ($name ? '/' . urlencode('{' . self::ITEM_CATEGORY_NS . '}' . $name) : null);
        return $this;
    }

    public function getCategory()
    {
        return $this->_category;
    }

    public function setGrouped($value)
    {
        if ($value !== null) {
            $this->_params['grouped'] = $value;
        }
        return $this;
    }

    public function getGrouped()
    {
        if (array_key_exists('grouped', $this->_params)) {
            return $this->_params['grouped'];
        } else {
            return null;
        }
    }

    public function setMaxResultsGroup($value)
    {
        if ($value !== null) {
            if ($value <= 0 || $this->getGrouped() !== 'true') {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'The max-results-group parameter must be set to a value
                    greater than 0 and can only be used if grouped=true'); 
            } else {
              $this->_params['max-results-group'] = $value;
            }
        }
        return $this;
    }

    public function getMaxResultsGroup()
    {
        if (array_key_exists('max-results-group', $this->_params)) {
            return $this->_params['max-results-group'];
        } else {
            return null;
        }
    }

    public function setMaxResultsInGroup($value)
    {
        if ($value !== null) {
            if ($value <= 0 || $this->getGrouped() !== 'true') {
              throw new Zend_Gdata_App_InvalidArgumentException(
                  'The max-results-in-group parameter must be set to a value 
                  greater than 0 and can only be used if grouped=true'); 
            } else {
              $this->_params['max-results-in-group'] = $value;
            }
        }
        return $this;
    }

    public function getMaxResultsInGroup()
    {
        if (array_key_exists('max-results-in-group', $this->_params)) {
            return $this->_params['max-results-in-group'];
        } else {
            return null;
        }
    }

    public function setStartIndexGroup($value)
    {
        if ($value !== null && $this->getGrouped() !== 'true') {
            throw new Zend_Gdata_App_InvalidArgumentException(
                'The start-index-group can only be used if grouped=true'); 
        } else {
          $this->_params['start-index-group'] = $value;
        }
        return $this;
    }

    public function getStartIndexGroup()
    {
        if (array_key_exists('start-index-group', $this->_params)) {
            return $this->_params['start-index-group'];
        } else {
            return null;
        }
    }

    public function setStartIndexInGroup($value)
    {
        if ($value !== null && $this->getGrouped() !== 'true') {
            throw new Zend_Gdata_App_InvalidArgumentException('start-index-in-group');
        } else {
          $this->_params['start-index-in-group'] = $value;
        }
        return $this;
    }

    public function getStartIndexInGroup()
    {
        if (array_key_exists('start-index-in-group', $this->_params)) {
            return $this->_params['start-index-in-group'];
        } else {
            return null;
        }
    }
}
