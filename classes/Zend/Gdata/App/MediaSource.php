<?php


interface Zend_Gdata_App_MediaSource
{

    public function encode();

    public function setContentType($value);

    public function getContentType();

    public function setSlug($value);

    public function getSlug();
}
