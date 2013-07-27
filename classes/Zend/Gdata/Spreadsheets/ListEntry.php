<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Spreadsheets/Extension/Custom.php';

class Zend_Gdata_Spreadsheets_ListEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Spreadsheets_ListEntry';

    protected $_custom = array();

    protected $_customByName = array();

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Spreadsheets::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if (!empty($this->_custom)) {
            foreach ($this->_custom as $custom) {
                $element->appendChild($custom->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        switch ($child->namespaceURI) {
        case $this->lookupNamespace('gsx');
            $custom = new Zend_Gdata_Spreadsheets_Extension_Custom($child->localName);
            $custom->transferFromDOM($child);
            $this->addCustom($custom);
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getCustom()
    {
        return $this->_custom;
    }

    public function getCustomByName($name = null)
    {
        if ($name === null) {
            return $this->_customByName;
        } else {
            if (array_key_exists($name, $this->customByName)) {
                return $this->_customByName[$name];
            } else {
                return null;
            }
        }
    }

    public function setCustom($custom)
    {
        $this->_custom = array();
        foreach ($custom as $c) {
            $this->addCustom($c);
        }
        return $this;
    }

    public function addCustom($custom)
    {
        $this->_custom[] = $custom;
        $this->_customByName[$custom->getColumnName()] = $custom;
        return $this;
    }

    public function removeCustom($index)
    {
        if (array_key_exists($index, $this->_custom)) {
            $element = $this->_custom[$index];
            // Remove element
            unset($this->_custom[$index]);
            // Re-index the array
            $this->_custom = array_values($this->_custom);
            // Be sure to delete form both arrays!
            $key = array_search($element, $this->_customByName);
            unset($this->_customByName[$key]);
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Element does not exist.');
        }
        return $this;
    }

    public function removeCustomByName($name)
    {
        if (array_key_exists($name, $this->_customByName)) {
            $element = $this->_customByName[$name];
            // Remove element
            unset($this->_customByName[$name]);
            // Be sure to delete from both arrays!
            $key = array_search($element, $this->_custom);
            unset($this->_custom[$key]);
        } else {
            require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Element does not exist.');
        }
        return $this;
    }

}
