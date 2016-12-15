<?php

use Sabre\DAV;

// The autoloader
require '../vendor/autoload.php';

require 'S3DAV/FS/File.php';
require 'S3DAV/FS/Directory.php';


$s3client = Aws\S3\S3Client::factory(array(
  'key' => 'YOUR_KEY',
  'secret' => 'YOU SECRET'
));

// Now we're creating a whole bunch of objects
$rootDirectory = new S3Directory('', 'audionamix-thirdparties', $s3client);

// The server object is responsible for making sense out of the WebDAV protocol
$server = new DAV\Server($rootDirectory);

// If your server is not on your webroot, make sure the following line has the
// correct information
$server->setBaseUri('/server/server.php');

// This ensures that we get a pretty index in the browser, but it is
// optional.
$server->addPlugin(new DAV\Browser\Plugin());

// All we need to do now, is to fire up the server
$server->exec();

?>
