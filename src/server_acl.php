<?php

use
    Sabre\DAV,
    Sabre\DAVACL;

// The autoloader
require '../vendor/autoload.php';

require 'DAV/FS/S3File.php';
require 'DAV/FS/S3Directory.php';

require 'DAVACL/FS/S3File.php';
require 'DAVACL/FS/S3Directory.php';
require 'DAVACL/FS/S3PrivateDirectory.php';

// initialize s3 client
$s3client = Aws\S3\S3Client::factory(array(
  'key' => '<Your AWS Key>',
  'secret' => '<Your AWS Secret>'
));
$s3bucket = '<Your AWS Bucket Name>';

// initilize database
$pdo = new \PDO('sqlite:/data/db.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

// init principals backend
$principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);

// Creating the auth backend.
$authBackend = new DAV\Auth\Backend\PDO($pdo);
$authBackend->setRealm('SabreDAV');
$authPlugin = new DAV\Auth\Plugin($authBackend);

// Now we're creating a whole bunch of objects
$tree = [new Sabre\DAVACL\PrincipalCollection($principalBackend)];

// For each users, add a S3PrivateDirectory to get a folder with read/write access to user only
foreach ($principalBackend->getPrincipalsByPrefix('principals') as $principalInfo) {
    array_push($tree, new DAVACL\FS\S3PrivateDirectory('', $s3bucket, $s3client, $principalInfo));
}

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($tree);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri('/server/server_acl.php');

// This ensures that we get a pretty index in the browser, but it is
// optional.
$server->addPlugin(new DAV\Browser\Plugin());

// enable ACL
$aclPlugin = new \Sabre\DAVACL\Plugin();
$server->addPlugin($aclPlugin);

// Add auth plugin
$server->addPlugin($authPlugin);

// All we need to do now, is to fire up the server
$server->exec();

?>
