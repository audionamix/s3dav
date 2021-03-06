FROM nimmis/apache-php5
MAINTAINER Audionamix <guillaume.vincke@audionamix.com>

WORKDIR /var/www/html
RUN rm index.html
ADD composer.json .
RUN composer install
RUN mkdir /public
RUN mkdir /data
RUN chmod a+rwx /data /public

RUN sed -i "s/post_max_size/;post_max_size/" /etc/php5/apache2/php.ini
RUN sed -i "s/upload_max_filesize/;upload_max_filesize/" /etc/php5/apache2/php.ini
RUN echo 'post_max_size = 1000M' >> /etc/php5/apache2/php.ini
RUN echo 'upload_max_filesize = 1000M' >> /etc/php5/apache2/php.ini
