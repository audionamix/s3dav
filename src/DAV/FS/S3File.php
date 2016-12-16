<?php

namespace Sabre\DAV\FS;

use Sabre\DAV;

/**
 * S3File class
 *
 */
class S3File extends DAV\File {

    protected $path;
    protected $bucket;
    protected $client;

    /**
     * Constructor
     *
     * @param string $path the path of the file
     * @param string $bucket the name of the s3 bucket
     * @param Aws\S3\S3Client $client an initialized S3 Client from AWS PHP SDK
     */
    function __construct($path, $bucket, $client) {
        $this->path = $path;
        $this->bucket = $bucket;
        $this->client = $client;
    }

    /**
     * Updates the data
     *
     * @param resource $data
     * @return void
     */
    function put($data) {
        $this->client->putObject(array(
            'Bucket' => $this->bucket,
            'Key' => $this->path,
            'Body' => $data,
        ));
    }

    /**
     * Returns the data
     *
     * @return resource
     */
    function get() {
        $result = $this->client->getObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $this->path,
        ));
        return (string) $result['Body'];
    }

    /**
     * Delete the current file
     *
     * @return void
     */
    function delete() {
        $this->client->deleteObject(array(
            'Bucket' => $this->bucket,
            'Key' => $this->path,
        ));
    }

    /**
     * Get current file name
     *
     * @return string
     */
    function getName() {
        return basename($this->path);
    }
}

?>
