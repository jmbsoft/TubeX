<?php


require_once 'Zend/Gdata/Entry.php';

require_once 'Zend/Gdata/Calendar/Extension/AccessLevel.php';

require_once 'Zend/Gdata/Calendar/Extension/Color.php';

require_once 'Zend/Gdata/Calendar/Extension/Hidden.php';

require_once 'Zend/Gdata/Calendar/Extension/Selected.php';

require_once 'Zend/Gdata/Extension/EventStatus.php';

require_once 'Zend/Gdata/Extension/Visibility.php';

require_once 'Zend/Gdata/Extension/Where.php';

class Zend_Gdata_Calendar_ListEntry extends Zend_Gdata_Entry
{

    protected $_color = null;
    protected $_accessLevel = null;
    protected $_hidden = null;
    protected $_selected = null;
    protected $_timezone = null;
    protected $_where = array();

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_accessLevel != null) {
            $element->appendChild($this->_accessLevel->getDOM($element->ownerDocument));
        }
        if ($this->_color != null) {
            $element->appendChild($this->_color->getDOM($element->ownerDocument));
        }
        if ($this->_hidden != null) {
            $element->appendChild($this->_hidden->getDOM($element->ownerDocument));
        }
        if ($this->_selected != null) {
            $element->appendChild($this->_selected->getDOM($element->ownerDocument));
        }
        if ($this->_timezone != null) {
            $element->appendChild($this->_timezone->getDOM($element->ownerDocument));
        }
        if ($this->_where != null) {
            foreach ($this->_where as $where) {
                $element->appendChild($where->getDOM($element->ownerDocument));
            }
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
        case $this->lookupNamespace('gCal') . ':' . 'accesslevel';
            $accessLevel = new Zend_Gdata_Calendar_Extension_AccessLevel();
            $accessLevel->transferFromDOM($child);
            $this->_accessLevel = $accessLevel;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'color';
            $color = new Zend_Gdata_Calendar_Extension_Color();
            $color->transferFromDOM($child);
            $this->_color = $color;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'hidden';
            $hidden = new Zend_Gdata_Calendar_Extension_Hidden();
            $hidden->transferFromDOM($child);
            $this->_hidden = $hidden;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'selected';
            $selected = new Zend_Gdata_Calendar_Extension_Selected();
            $selected->transferFromDOM($child);
            $this->_selected = $selected;
            break;
        case $this->lookupNamespace('gCal') . ':' . 'timezone';
            $timezone = new Zend_Gdata_Calendar_Extension_Timezone();
            $timezone->transferFromDOM($child);
            $this->_timezone = $timezone;
            break;
        case $this->lookupNamespace('gd') . ':' . 'where';
            $where = new Zend_Gdata_Extension_Where();
            $where->transferFromDOM($child);
            $this->_where[] = $where;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    public function getAccessLevel()
    {
        return $this->_accessLevel;
    }

    public function setAccessLevel($value)
    {
        $this->_accessLevel = $value;
        return $this;
    }
    public function getColor()
    {
        return $this->_color;
    }

    public function setColor($value)
    {
        $this->_color = $value;
        return $this;
    }

    public function getHidden()
    {
        return $this->_hidden;
    }

    public function setHidden($value)
    {
        $this->_hidden = $value;
        return $this;
    }

    public function getSelected()
    {
        return $this->_selected;
    }

    public function setSelected($value)
    {
        $this->_selected = $value;
        return $this;
    }

    public function getTimezone()
    {
        return $this->_timezone;
    }

    public function setTimezone($value)
    {
        $this->_timezone = $value;
        return $this;
    }

    public function getWhere()
    {
        return $this->_where;
    }

    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }

}
