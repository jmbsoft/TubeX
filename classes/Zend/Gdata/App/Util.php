<?php


class Zend_Gdata_App_Util
{

    public static function formatTimestamp($timestamp)
    {
        $rfc3339 = '/^(\d{4})\-?(\d{2})\-?(\d{2})((T|t)(\d{2})\:?(\d{2})' .
                   '\:?(\d{2})(\.\d{1,})?((Z|z)|([\+\-])(\d{2})\:?(\d{2})))?$/';

        if (ctype_digit($timestamp)) {
            return gmdate('Y-m-d\TH:i:sP', $timestamp);
        } elseif (preg_match($rfc3339, $timestamp) > 0) {
            // timestamp is already properly formatted
            return $timestamp;
        } else {
            $ts = strtotime($timestamp);
            if ($ts === false) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException("Invalid timestamp: $timestamp.");
            }
            return date('Y-m-d\TH:i:s', $ts);
        }
    }

    public static function findGreatestBoundedValue($maximumKey, $collection)
    {
        $found = false;
        $foundKey = $maximumKey;

        // Sanity check: Make sure that the collection isn't empty
        if (sizeof($collection) == 0) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("Empty namespace collection encountered.");
        }

        if (is_null($maximumKey)) {
            // If the key is null, then we return the maximum available
            $keys = array_keys($collection);
            sort($keys);
            $found = true;
            $foundKey = end($keys);
        } else {
            // Otherwise, we optimistically guess that the current version
            // will have a matching namespce. If that fails, we decrement the
            // version until we find a match.
            while (!$found && $foundKey >= 0) {
                if (array_key_exists($foundKey, $collection))
                    $found = true;
                else
                    $foundKey--;
            }
        }

        // Guard: A namespace wasn't found. Either none were registered, or
        // the current protcol version is lower than the maximum namespace.
        if (!$found) {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("Namespace compatible with current protocol not found.");
        }

        return $foundKey;
    }

}
