<?php

class S3File extends Sabre\DAV\File {

  private $myPath;

  function __construct($myPath) {

    $this->myPath = $myPath;

  }

  function getName() {

    return basename($this->myPath);

  }

  function get() {

    return fopen($this->myPath,'r');

  }

  function getSize() {

    return filesize($this->myPath);

  }

  function getETag() {

    return '"' . md5_file($this->myPath) . '"';

  }

}

 ?>
