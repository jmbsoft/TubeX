<?php


require_once 'Zend/Validate/Abstract.php';

class Zend_Validate_Date extends Zend_Validate_Abstract
{

    const NOT_YYYY_MM_DD = 'dateNotYYYY-MM-DD';

    const INVALID        = 'dateInvalid';

    const FALSEFORMAT    = 'dateFalseFormat';

    protected $_messageTemplates = array(
        self::NOT_YYYY_MM_DD => "'%value%' is not of the format YYYY-MM-DD",
        self::INVALID        => "'%value%' does not appear to be a valid date",
        self::FALSEFORMAT    => "'%value%' does not fit given date format"
    );

    protected $_format;

    protected $_locale;

    public function __construct($format = null, $locale = null)
    {
        $this->setFormat($format);
        if ($locale !== null) {
            $this->setLocale($locale);
        }
    }

    public function getLocale()
    {
        return $this->_locale;
    }

    public function setLocale($locale = null)
    {
        require_once 'Zend/Locale.php';
        $this->_locale = Zend_Locale::findLocale($locale);
        return $this;
    }

    public function getFormat()
    {
        return $this->_format;
    }

    public function setFormat($format = null)
    {
        $this->_format = $format;
        return $this;
    }

    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if (($this->_format !== null) or ($this->_locale !== null)) {
            require_once 'Zend/Date.php';
            if (!Zend_Date::isDate($value, $this->_format, $this->_locale)) {
                if ($this->_checkFormat($value) === false) {
                    $this->_error(self::FALSEFORMAT);
                } else {
                    $this->_error(self::INVALID);
                }
                return false;
            }
        } else {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valueString)) {
                $this->_error(self::NOT_YYYY_MM_DD);
                return false;
            }

            list($year, $month, $day) = sscanf($valueString, '%d-%d-%d');

            if (!checkdate($month, $day, $year)) {
                $this->_error(self::INVALID);
                return false;
            }
        }

        return true;
    }

    private function _checkFormat($value)
    {
        try {
            require_once 'Zend/Locale/Format.php';
            $parsed = Zend_Locale_Format::getDate($value, array(
                                                  'date_format' => $this->_format, 'format_type' => 'iso',
                                                  'fix_date' => false));
            if (isset($parsed['year']) and ((strpos(strtoupper($this->_format), 'YY') !== false) and
                (strpos(strtoupper($this->_format), 'YYYY') === false))) {
                $parsed['year'] = Zend_Date::getFullYear($parsed['year']);
            }
        } catch (Exception $e) {
            // Date can not be parsed
            return false;
        }

        if (((strpos($this->_format, 'Y') !== false) or (strpos($this->_format, 'y') !== false)) and
            (!isset($parsed['year']))) {
            // Year expected but not found
            return false;
        }

        if ((strpos($this->_format, 'M') !== false) and (!isset($parsed['month']))) {
            // Month expected but not found
            return false;
        }

        if ((strpos($this->_format, 'd') !== false) and (!isset($parsed['day']))) {
            // Day expected but not found
            return false;
        }

        if (((strpos($this->_format, 'H') !== false) or (strpos($this->_format, 'h') !== false)) and
            (!isset($parsed['hour']))) {
            // Hour expected but not found
            return false;
        }

        if ((strpos($this->_format, 'm') !== false) and (!isset($parsed['minute']))) {
            // Minute expected but not found
            return false;
        }

        if ((strpos($this->_format, 's') !== false) and (!isset($parsed['second']))) {
            // Second expected  but not found
            return false;
        }

        // Date fits the format
        return true;
    }
}
