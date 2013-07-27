<?php


abstract class Zend_Search_Lucene_Storage_Directory
{

    abstract public function close();

    abstract public function fileList();

    abstract public function createFile($filename);

    abstract public function deleteFile($filename);

    abstract public function purgeFile($filename);

    abstract public function fileExists($filename);

    abstract public function fileLength($filename);

    abstract public function fileModified($filename);

    abstract public function renameFile($from, $to);

    abstract public function touchFile($filename);

    abstract public function getFileObject($filename, $shareHandler = true);

}

