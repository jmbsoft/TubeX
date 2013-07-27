<?php


require_once 'Zend/Gdata/Gbase/Entry.php';

class Zend_Gdata_Gbase_ItemEntry extends Zend_Gdata_Gbase_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Gbase_ItemEntry';

    public function setItemType($value)
    {
        $this->addGbaseAttribute('item_type', $value, 'text');
        return $this;
    }

    public function addGbaseAttribute($name, $text, $type = null) {
        $newBaseAttribute =  new Zend_Gdata_Gbase_Extension_BaseAttribute($name, $text, $type);
        $this->_baseAttributes[] = $newBaseAttribute;
        return $this;
    }

    public function removeGbaseAttribute($baseAttribute) {
        $baseAttributes = $this->_baseAttributes;
        for ($i = 0; $i < count($this->_baseAttributes); $i++) {
            if ($this->_baseAttributes[$i] == $baseAttribute) {
                array_splice($baseAttributes, $i, 1);
                break;
            }
        }
        $this->_baseAttributes = $baseAttributes;
        return $this;
    }

    public function save($dryRun = false,
                         $uri = null,
                         $className = null,
                         $extraHeaders = array())
    {
        if ($dryRun == true) {
            $editLink = $this->getEditLink();
            if ($uri == null && $editLink !== null) {
                $uri = $editLink->getHref() . '?dry-run=true';
            }
            if ($uri === null) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
            $service = new Zend_Gdata_App($this->getHttpClient());
            return $service->updateEntry($this,
                                         $uri,
                                         $className,
                                         $extraHeaders);
        } else {
            parent::save($uri, $className, $extraHeaders);
        }
    }

    public function delete($dryRun = false)
    {
        $uri = null;

        if ($dryRun == true) {
            $editLink = $this->getEditLink();
            if ($editLink !== null) {
                $uri = $editLink->getHref() . '?dry-run=true';
            }
            if ($uri === null) {
                require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException('You must specify an URI which needs deleted.');
            }
            parent::delete($uri);
        } else {
            parent::delete();
        }
    }

}
