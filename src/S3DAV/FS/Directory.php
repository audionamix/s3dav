<?php

use Sabre\DAV;

class S3Directory extends DAV\Collection {

  private $client;
  private $bucket;
  private $path;
  private $objects;

  function __construct($path, $bucket, $client) {
    // remove first '/' char if needed
    $this->path = ltrim($path, '/');
    $this->bucket = $bucket;
    $this->client = $client;

    # list objects at path
    $this->objects = $this->client->getIterator('ListObjects', array(
        'Bucket' => $this->bucket,
        'Delimiter' => '/',
        'Prefix' => $this->path
      ),
      array(
        'return_prefixes' => true,
      )
    );
  }

  function isDirectory($name) {
    foreach ($this->objects as $object) {
      if (isset($object['Prefix'])) {
        if (strcmp($name, basename($object['Prefix'])) == 0) {
          return true;
        }
      }
    }
    return false;
  }

  function isRegularFile($name) {
    foreach ($this->objects as $object) {
      if (isset($object['Key'])) {
        if (strcmp($name, basename($object['Key'])) == 0) {
          return true;
        }
      }
    }
    return false;
  }

  function getChildren() {
    $children = array();

    foreach ($this->objects as $object) {
      if (isset($object['Prefix'])) {
        $children[] = new S3Directory($object['Prefix'], $this->bucket, $this->client);
      } else {
        $children[] = new S3File($object['Key']);
      }
    }

    return $children;
  }

  function getChild($name) {
    $path = $name . '/';
    if (!empty($this->path)) {
      $path = $this->path . $name . '/';
    }

    if ($this->isDirectory($name)) {
      return new S3Directory($path, $this->bucket, $this->client);
    }
    if ($this->isRegularFile($name)) {
      return new S3File($path);
    }
    throw new DAV\Exception\NotFound('The file with name: ' . $name . ' could not be found');
  }

  function childExists($name) {

        return file_exists($this->myPath . '/' . $name);

  }

  function getName() {

      return basename($this->path);

  }

}


?>
