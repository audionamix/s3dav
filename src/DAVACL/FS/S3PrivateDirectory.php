<?php

namespace Sabre\DAVACL\FS;

use Sabre\DAV;
use Sabre\DAVACL;

class S3PrivateDirectory extends DAVACL\FS\S3Directory {
    /**
     * Constructor
     *
     * @param string $path the path of the file
     * @param string $bucket the name of the s3 bucket
     * @param Aws\S3\S3Client $client an initialized S3 Client from AWS PHP SDK
     * @param principal $principal the principal that wll have full access to the folder
     */
    function __construct($path, $bucket, $client, $principal) {
        $owner = $principal['uri'];
        $ownerName = $principal['{DAV:}displayname'];

        // create S3Directory if doesn't exists yet
        $rootDirectory = new DAV\FS\S3Directory($path, $bucket, $client);
        $rootDirectory->createDirectory($ownerName);

        // Restrict access to other directories
        $acl = [
                [
                    'privilege' => '{DAV:}read',
                    'principal' => $owner,
                    'protected' => true,
                ],
                [
                    'privilege' => '{DAV:}write',
                    'principal' => $owner,
                    'protected' => true,
                ],
            ];
        parent::__construct($path . '/' . $ownerName . '/', $bucket, $client, $acl, $owner);
    }
}

?>
