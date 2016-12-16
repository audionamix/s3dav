<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV;
use Sabre\DAV\FS\S3Directory as S3BaseDirectory;

class S3Directory extends S3BaseDirectory implements \Sabre\DAVACL\IACL {
    private $acl;
    private $owner;

    /**
     * Constructor
     *
     * @param string $path the path of the folder
     * @param string $bucket the name of the s3 bucket
     * @param Aws\S3\S3Client $client an initialized S3 Client from AWS PHP SDK
     * @param array $acl ACL rules.
     * @param string|null $owner principal owner string.
     */
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

    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    function getOwner() {
        return $this->owner;
    }

    /**
     * Returns a group principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    function getGroup() {
        return null;
    }

    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to
     *      be updated.
     *
     * @return array
     */
    function getACL() {
        return $this->acl;
    }

    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's as an array argument.
     *
     * @param array $acl
     * @return void
     */
    function setACL(array $acl) {
        throw new Forbidden('Setting ACL is not allowed here');
    }

    /**
     * Returns the list of supported privileges for this node.
     *
     * The returned data structure is a list of nested privileges.
     * See Sabre\DAVACL\Plugin::getDefaultSupportedPrivilegeSet for a simple
     * standard structure.
     *
     * If null is returned from this method, the default privilege set is used,
     * which is fine for most common usecases.
     *
     * @return array|null
     */
    function getSupportedPrivilegeSet() {
        return null;
    }
}

?>
