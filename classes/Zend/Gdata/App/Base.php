<?php


require_once 'Zend/Gdata/App/Util.php';

abstract class Zend_Gdata_App_Base
{

    protected $_rootElement = null;

    protected $_rootNamespace = 'atom';

    protected $_rootNamespaceURI = null;

    protected $_extensionElements = array();

    protected $_extensionAttributes = array();

    protected $_text = null;

    protected static $_namespaceLookupCache = array();

   protected $_namespaces = array(
        'atom'      => array(
            1 => array(
                0 => 'http://www.w3.org/2005/Atom'
                )
            ),
        'app'       => array(
            1 => array(
                0 => 'http://purl.org/atom/app#'
                ),
            2 => array(
                0 => 'http://www.w3.org/2007/app'
                )
            )
        );

    public function __construct()
    {
    }

    public function getText($trim = true)
    {
        if ($trim) {
            return trim($this->_text);
        } else {
            return $this->_text;
        }
    }

    public function setText($value)
    {
        $this->_text = $value;
        return $this;
    }

    public function getExtensionElements()
    {
        return $this->_extensionElements;
    }

    public function setExtensionElements($value)
    {
        $this->_extensionElements = $value;
        return $this;
    }

    public function getExtensionAttributes()
    {
        return $this->_extensionAttributes;
    }

    public function setExtensionAttributes($value)
    {
        $this->_extensionAttributes = $value;
        return $this;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        if (is_null($doc)) {
            $doc = new DOMDocument('1.0', 'utf-8');
        }
        if ($this->_rootNamespaceURI != null) {
            $element = $doc->createElementNS($this->_rootNamespaceURI, $this->_rootElement);
        } elseif ($this->_rootNamespace !== null) {
            if (strpos($this->_rootElement, ':') === false) {
                $elementName = $this->_rootNamespace . ':' . $this->_rootElement;
            } else {
                $elementName = $this->_rootElement;
            }
            $element = $doc->createElementNS($this->lookupNamespace($this->_rootNamespace), $elementName);
        } else {
            $element = $doc->createElement($this->_rootElement);
        }
        if ($this->_text != null) {
            $element->appendChild($element->ownerDocument->createTextNode($this->_text));
        }
        foreach ($this->_extensionElements as $extensionElement) {
            $element->appendChild($extensionElement->getDOM($element->ownerDocument));
        }
        foreach ($this->_extensionAttributes as $attribute) {
            $element->setAttribute($attribute['name'], $attribute['value']);
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        if ($child->nodeType == XML_TEXT_NODE) {
            $this->_text = $child->nodeValue;
        } else {
            $extensionElement = new Zend_Gdata_App_Extension_Element();
            $extensionElement->transferFromDOM($child);
            $this->_extensionElements[] = $extensionElement;
        }
    }

    protected function takeAttributeFromDOM($attribute)
    {
        $arrayIndex = ($attribute->namespaceURI != '')?(
                $attribute->namespaceURI . ':' . $attribute->name):
                $attribute->name;
        $this->_extensionAttributes[$arrayIndex] =
                array('namespaceUri' => $attribute->namespaceURI,
                      'name' => $attribute->localName,
                      'value' => $attribute->nodeValue);
    }

    public function transferFromDOM($node)
    {
        foreach ($node->childNodes as $child) {
            $this->takeChildFromDOM($child);
        }
        foreach ($node->attributes as $attribute) {
            $this->takeAttributeFromDOM($attribute);
        }
    }

    public function transferFromXML($xml)
    {
        if ($xml) {
            // Load the feed as an XML DOMDocument object
            @ini_set('track_errors', 1);
            $doc = new DOMDocument();
            $success = @$doc->loadXML($xml);
            @ini_restore('track_errors');
            if (!$success) {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception("DOMDocument cannot parse XML: $php_errormsg");
            }
            $element = $doc->getElementsByTagName($this->_rootElement)->item(0);
            if (!$element) {
                require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception('No root <' . $this->_rootElement . '> element');
            }
            $this->transferFromDOM($element);
        } else {
            require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('XML passed to transferFromXML cannot be null');
        }
    }

    public function saveXML()
    {
        $element = $this->getDOM();
        return $element->ownerDocument->saveXML($element);
    }

    public function getXML()
    {
        return $this->saveXML();
    }

    public function encode()
    {
        return $this->saveXML();
    }

    public function lookupNamespace($prefix,
                                    $majorVersion = 1,
                                    $minorVersion = null)
    {
        // Check for a memoized result
        $key = $prefix . ' ' .
               (is_null($majorVersion) ? 'NULL' : $majorVersion) .
               ' '. (is_null($minorVersion) ? 'NULL' : $minorVersion);
        if (array_key_exists($key, self::$_namespaceLookupCache))
          return self::$_namespaceLookupCache[$key];
        // If no match, return the prefix by default
        $result = $prefix;

        // Find tuple of keys that correspond to the namespace we should use
        if (isset($this->_namespaces[$prefix])) {
            // Major version search
            $nsData = $this->_namespaces[$prefix];
            $foundMajorV = Zend_Gdata_App_Util::findGreatestBoundedValue(
                    $majorVersion, $nsData);
            // Minor version search
            $nsData = $nsData[$foundMajorV];
            $foundMinorV = Zend_Gdata_App_Util::findGreatestBoundedValue(
                    $minorVersion, $nsData);
            // Extract NS
            $result = $nsData[$foundMinorV];
        }

        // Memoize result
        self::$_namespaceLookupCache[$key] = $result;

        return $result;
    }

    public function registerNamespace($prefix,
                                      $namespaceUri,
                                      $majorVersion = 1,
                                      $minorVersion = 0)
    {
        $this->_namespaces[$prefix][$majorVersion][$minorVersion] =
        $namespaceUri;
    }

    public static function flushNamespaceLookupCache()
    {
        self::$_namespaceLookupCache = array();
    }

    public function registerAllNamespaces($namespaceArray)
    {
        foreach($namespaceArray as $namespace) {
                $this->registerNamespace(
                    $namespace[0], $namespace[1], $namespace[2], $namespace[3]);
        }
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

    public function __unset($name)
    {
        if (isset($this->{'_' . $name})) {
            if (is_array($this->{'_' . $name})) {
                $this->{'_' . $name} = array();
            } else {
                $this->{'_' . $name} = null;
            }
        }
    }

    public function __toString()
    {
        return $this->getText();
    }

}
