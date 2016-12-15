# s3dav

An extension to [SabreDAV](http://sabre.io/dav/) that let you store files on an s3 bucket

### Development

Before trying the server, you need to configure your AWS credentials. To do so, please update the `<Your AWS Key>` `<Your AWS Secret>` and `<Your AWS Bucket Name>` fields.  

Once updated, `docker-compose up` let you start the test webdav server on http://localhost:8080/server/server.php  

To get the apache error logs, you'll need to get the container ID (`docker ps`) and tail the log from that container  
`docker exec -it <container id> tail -f /var/log/apache2/error.log`
