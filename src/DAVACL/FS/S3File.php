<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV;
use Sabre\DAV\FS\S3File as S3BaseFile;

class S3File extends S3BaseFile implements \Sabre\DAVACL\IACL {
    private $acl;
    private $owner;

    /**
     * Constructor
     *
     * @param string $path the path of the file
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
