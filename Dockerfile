FROM nimmis/apache-php5
MAINTAINER Audionamix <guillaume.vincke@audionamix.com>

RUN sudo apt-get -y update
RUN sudo apt-get -y upgrade
RUN sudo apt-get install -y sqlite3 libsqlite3-dev

WORKDIR /var/www/html
RUN rm index.html
ADD composer.json .
RUN composer install
RUN mkdir /public
RUN mkdir /data
RUN chmod a+rwx /data /public
