<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV;
use Sabre\DAV\FS\S3File as S3BaseFile;

class S3File extends S3BaseFile implements \Sabre\DAVACL\IACL {
    private $acl;
    private $owner;

    function __construct($path, $bucket, $client, array $acl, $owner = null) {
        parent::__construct($path, $bucket, $client);
        $this->acl = $acl;
        $this->owner = $owner;
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
