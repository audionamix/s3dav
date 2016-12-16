<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV;
use Sabre\DAV\FS\S3Directory as S3BaseDirectory;

class S3Directory extends S3BaseDirectory implements \Sabre\DAVACL\IACL {
    private $acl;
    private $owner;

    function __construct($path, $bucket, $client, array $acl, $owner = null) {
        parent::__construct($path, $bucket, $client);
        $this->acl = $acl;
        $this->owner = $owner;
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
            return new S3Directory($path . '/', $this->bucket, $this->client, $this->acl, $this->owner);
        }
        if ($this->isRegularFile($name)) {
            return new S3File($path, $this->bucket, $this->client, $this->acl, $this->owner);
        }

        // if not a file nor a directory, throw an exception
        throw new DAV\Exception\NotFound('The file with name: ' . $name . ' could not be found');
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
                $children[] = new S3Directory($object['Prefix'], $this->bucket, $this->client, $this->acl, $this->owner);
            } else {
                // we always get one child that is the current path. Don't include it
                if (strcmp($object['Key'], $this->path) == 0) continue;
                $children[] = new S3File($object['Key'], $this->bucket, $this->client, $this->acl, $this->owner);
            }
        }

        return $children;
    }

    function getOwner() {
        return $this->owner;
    }

    function getGroup() {
        return null;
    }

    function getACL() {
        return $this->acl;
    }

    function setACL(array $acl) {
        throw new Forbidden('Setting ACL is not allowed here');
    }

    function getSupportedPrivilegeSet() {
        return null;
    }
}

?>
