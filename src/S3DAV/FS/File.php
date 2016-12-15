<?php

class S3File extends Sabre\DAV\File {

  private $path;
  private $bucket;
  private $client;

  function __construct($path, $bucket, $client) {
    $this->path = $path;
    $this->bucket = $bucket;
    $this->client = $client;
  }

  function getName() {
    return basename($this->path);
  }

  function get() {
    $result = $this->client->getObject(array(
      'Bucket' => $this->bucket,
      'Key'    => $this->path,
    ));
    return (string) $result['Body'];
  }

  function getSize() {
    return filesize($this->path);
  }

  function getETag() {
    return '"' . md5_file($this->path) . '"';
  }

  function put($data) {
    $this->client->putObject(array(
        'Bucket' => $this->bucket,
        'Key' => $this->path,
        'Body' => $data,
    ));
  }

  function delete() {
    $this->client->deleteObject(array(
        'Bucket' => $this->bucket,
        'Key' => $this->path,
    ));
  }
}

 ?>
