<?php


require_once 'Zend/Gdata/Extension.php';

require_once 'Zend/Gdata/Exif.php';

require_once 'Zend/Gdata/Exif/Extension/Distance.php';

require_once 'Zend/Gdata/Exif/Extension/Exposure.php';

require_once 'Zend/Gdata/Exif/Extension/Flash.php';

require_once 'Zend/Gdata/Exif/Extension/FocalLength.php';

require_once 'Zend/Gdata/Exif/Extension/FStop.php';

require_once 'Zend/Gdata/Exif/Extension/ImageUniqueId.php';

require_once 'Zend/Gdata/Exif/Extension/Iso.php';

require_once 'Zend/Gdata/Exif/Extension/Make.php';

require_once 'Zend/Gdata/Exif/Extension/Model.php';

require_once 'Zend/Gdata/Exif/Extension/Time.php';

class Zend_Gdata_Exif_Extension_Tags extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'exif';
    protected $_rootElement = 'tags';

    protected $_distance = null;

    protected $_exposure = null;

    protected $_flash = null;

    protected $_focalLength = null;

    protected $_fStop = null;

    protected $_imageUniqueId = null;

    protected $_iso = null;

    protected $_make = null;

    protected $_model = null;

    protected $_time = null;

    public function __construct($distance = null, $exposure = null,
            $flash = null, $focalLength = null, $fStop = null,
            $imageUniqueId = null, $iso = null, $make = null,
            $model = null, $time = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Exif::$namespaces);
        parent::__construct();
        $this->setDistance($distance);
        $this->setExposure($exposure);
        $this->setFlash($flash);
        $this->setFocalLength($focalLength);
        $this->setFStop($fStop);
        $this->setImageUniqueId($imageUniqueId);
        $this->setIso($iso);
        $this->setMake($make);
        $this->setModel($model);
        $this->setTime($time);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_distance !== null) {
            $element->appendChild($this->_distance->getDOM($element->ownerDocument));
        }
        if ($this->_exposure !== null) {
            $element->appendChild($this->_exposure->getDOM($element->ownerDocument));
        }
        if ($this->_flash !== null) {
            $element->appendChild($this->_flash->getDOM($element->ownerDocument));
        }
        if ($this->_focalLength !== null) {
            $element->appendChild($this->_focalLength->getDOM($element->ownerDocument));
        }
        if ($this->_fStop !== null) {
            $element->appendChild($this->_fStop->getDOM($element->ownerDocument));
        }
        if ($this->_imageUniqueId !== null) {
            $element->appendChild($this->_imageUniqueId->getDOM($element->ownerDocument));
        }
        if ($this->_iso !== null) {
            $element->appendChild($this->_iso->getDOM($element->ownerDocument));
        }
        if ($this->_make !== null) {
            $element->appendChild($this->_make->getDOM($element->ownerDocument));
        }
        if ($this->_model !== null) {
            $element->appendChild($this->_model->getDOM($element->ownerDocument));
        }
        if ($this->_time !== null) {
            $element->appendChild($this->_time->getDOM($element->ownerDocument));
        }
        return $element;
    }

    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('exif') . ':' . 'distance';
                $distance = new Zend_Gdata_Exif_Extension_Distance();
                $distance->transferFromDOM($child);
                $this->_distance = $distance;
                break;
            case $this->lookupNamespace('exif') . ':' . 'exposure';
                $exposure = new Zend_Gdata_Exif_Extension_Exposure();
                $exposure->transferFromDOM($child);
                $this->_exposure = $exposure;
                break;
            case $this->lookupNamespace('exif') . ':' . 'flash';
                $flash = new Zend_Gdata_Exif_Extension_Flash();
                $flash->transferFromDOM($child);
                $this->_flash = $flash;
                break;
            case $this->lookupNamespace('exif') . ':' . 'focallength';
                $focalLength = new Zend_Gdata_Exif_Extension_FocalLength();
                $focalLength->transferFromDOM($child);
                $this->_focalLength = $focalLength;
                break;
            case $this->lookupNamespace('exif') . ':' . 'fstop';
                $fStop = new Zend_Gdata_Exif_Extension_FStop();
                $fStop->transferFromDOM($child);
                $this->_fStop = $fStop;
                break;
            case $this->lookupNamespace('exif') . ':' . 'imageUniqueID';
                $imageUniqueId = new Zend_Gdata_Exif_Extension_ImageUniqueId();
                $imageUniqueId->transferFromDOM($child);
                $this->_imageUniqueId = $imageUniqueId;
                break;
            case $this->lookupNamespace('exif') . ':' . 'iso';
                $iso = new Zend_Gdata_Exif_Extension_Iso();
                $iso->transferFromDOM($child);
                $this->_iso = $iso;
                break;
            case $this->lookupNamespace('exif') . ':' . 'make';
                $make = new Zend_Gdata_Exif_Extension_Make();
                $make->transferFromDOM($child);
                $this->_make = $make;
                break;
            case $this->lookupNamespace('exif') . ':' . 'model';
                $model = new Zend_Gdata_Exif_Extension_Model();
                $model->transferFromDOM($child);
                $this->_model = $model;
                break;
            case $this->lookupNamespace('exif') . ':' . 'time';
                $time = new Zend_Gdata_Exif_Extension_Time();
                $time->transferFromDOM($child);
                $this->_time = $time;
                break;
        }
    }

    public function getDistance()
    {
        return $this->_distance;
    }

    public function setDistance($value)
    {
        $this->_distance = $value;
        return $this;
    }

    public function getExposure()
    {
        return $this->_exposure;
    }

    public function setExposure($value)
    {
        $this->_exposure = $value;
        return $this;
    }

    public function getFlash()
    {
        return $this->_flash;
    }

    public function setFlash($value)
    {
        $this->_flash = $value;
        return $this;
    }

    public function getFocalLength()
    {
        return $this->_focalLength;
    }

    public function setFocalLength($value)
    {
        $this->_focalLength = $value;
        return $this;
    }

    public function getFStop()
    {
        return $this->_fStop;
    }

    public function setFStop($value)
    {
        $this->_fStop = $value;
        return $this;
    }

    public function getImageUniqueId()
    {
        return $this->_imageUniqueId;
    }

    public function setImageUniqueId($value)
    {
        $this->_imageUniqueId = $value;
        return $this;
    }

    public function getIso()
    {
        return $this->_iso;
    }

    public function setIso($value)
    {
        $this->_iso = $value;
        return $this;
    }

    public function getMake()
    {
        return $this->_make;
    }

    public function setMake($value)
    {
        $this->_make = $value;
        return $this;
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function setModel($value)
    {
        $this->_model = $value;
        return $this;
    }

    public function getTime()
    {
        return $this->_time;
    }

    public function setTime($value)
    {
        $this->_time = $value;
        return $this;
    }

}
