<?php

namespace Sabre\DAV\FS;

use Sabre\DAV;

/**
* S3Directory class
*
*/
class S3Directory extends DAV\Collection {
    protected $client;
    protected $bucket;
    protected $path;
    protected $objects;

    /**
     * Constructor
     *
     * @param string $path the path of the folder
     * @param string $bucket the name of the s3 bucket
     * @param Aws\S3\S3Client $client an initialized S3 Client from AWS PHP SDK
     */
    function __construct($path, $bucket, $client) {
        // remove first '/' char if any provided
        $this->path = ltrim($path, '/');
        $this->bucket = $bucket;
        $this->client = $client;

        # list objects at given path when building the Directory.
        # going that avoids listing several times
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

    /**
     * Create a new file in the directory
     *
     * @param string $name Name of the file
     * @param resource|string $data Initial payload
     * @return null
     */
    function createFile($name, $data = null) {
        $newPath = $this->path . $name;
        $newFile = new S3File($newPath, $this->bucket, $this->client);
        $newFile->put($data);
    }

    /**
     * Creates a new subdirectory
     *
     * @param string $name
     * @return void
     */
    function createDirectory($name) {
        $path = $this->path . $name . '/';
        $this->client->putObject(array(
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Body' => '',
        ));
    }

    /**
     * Returns a specific child node, referenced by its name
     *
     * This method throw DAV\Exception\NotFound if the node does not exist.
     *
     * @param string $name
     * @throws DAV\Exception\NotFound
     * @return DAV\INode
     */
    function getChild($name) {
        $path = $this->path . $name;

        if ($this->isDirectory($name)) {
            return new S3Directory($path . '/', $this->bucket, $this->client);
        }
        if ($this->isRegularFile($name)) {
            return new S3File($path, $this->bucket, $this->client);
        }

        // if not a file nor a directory, throw an exception
        throw new DAV\Exception\NotFound('The file with name: ' . $name . ' could not be found');
    }

    /**
     * Checks if the path describes a directory
     *
     * @param name of the child
     * @return bool
     */
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


    /**
     * Checks if the path describes a file
     *
     * @param name of the child
     * @return bool
     */
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

    /**
     * Deletes all files in this directory, and then itself
     *
     * @return void
     */
    function delete() {
        // remove everything contained in the folder
        foreach ($this->objects as $object) {
            if (isset($object['Prefix'])) {
                $node = new S3Directory($object['Prefix'], $this->bucket, $this->client);
                $node->delete();
            } else {
                // we always get one child that is the current path. Don't include it
                if (strcmp($object['Key'], $this->path) == 0) continue;
                $node = new S3File($object['Key'], $this->bucket, $this->client);
                $node->delete();
            }
        }

        // remove itself
        $this->client->deleteObject(array(
            'Bucket' => $this->bucket,
            'Key' => $this->path,
        ));
    }

    /**
     * Returns an array with all the child nodes
     *
     * @return DAV\INode[]
     */
    function getChildren() {
        $children = array();

        foreach ($this->objects as $object) {
            if (isset($object['Prefix'])) {
                $children[] = new S3Directory($object['Prefix'], $this->bucket, $this->client);
            } else {
                // we always get one child that is the current path. Don't include it
                if (strcmp($object['Key'], $this->path) == 0) continue;
                $children[] = new S3File($object['Key'], $this->bucket, $this->client);
            }
        }

        return $children;
    }

    /**
     * Checks if a child exists.
     *
     * @param string $name
     * @return bool
     */
    function childExists($name) {
        return $this->isDirectory($name) || $this->isRegularFile($name);
    }

    /**
     * Get current directory name
     *
     * @return string
     */
    function getName() {
        return basename($this->path);
    }

}


?>
