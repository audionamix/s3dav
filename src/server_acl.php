<?php

use
    Sabre\DAV,
    Sabre\DAVACL;

// The autoloader
require '../vendor/autoload.php';

require 'DAV/FS/S3File.php';
require 'DAV/FS/S3Directory.php';

// initialize s3 client
$s3client = Aws\S3\S3Client::factory(array(
  'key' => '<Your AWS Key>',
  'secret' => '<Your AWS Secret>'
));

// initilize database
$pdo = new \PDO('sqlite:/data/db.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

// init principals backend
$principalBackend = new DAVACL\PrincipalBackend\PDO($pdo);

// Creating the auth backend.
$authBackend = new DAV\Auth\Backend\PDO($pdo);
$authBackend->setRealm('SabreDAV');

// Now we're creating a whole bunch of objects
$tree = [
    new DAVACL\FS\HomeCollection($principalBackend, "/public"),
    new DAVACL\PrincipalCollection($principalBackend),
];

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($tree);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri('/server/server_acl.php');

// This ensures that we get a pretty index in the browser, but it is
// optional.
$server->addPlugin(new DAV\Browser\Plugin());

// Add auth plugin
$server->addPlugin(new DAV\Auth\Plugin($authBackend));

// All we need to do now, is to fire up the server
$server->exec();

?>
