FROM nimmis/apache-php5
MAINTAINER Audionamix <guillaume.vincke@audionamix.com>

WORKDIR /var/www/html
RUN rm index.html
ADD composer.json .
RUN composer install
RUN mkdir /public
RUN mkdir /data
RUN chmod a+rwx /data /public
