FROM ubuntu:16.04

RUN apt-get update \
  && apt-get install -yqq apache2 php libapache2-mod-php php-mcrypt php-mysql git php-dom

WORKDIR /opt

ADD main_server/. /var/www/html/main_server

RUN echo "nameserver 8.8.8.8" >> /etc/resolv.conf \
  && chown -R www-data: /var/www/html

#RUN chmod 0644 /var/www/html/auto-config.inc.php

CMD ["apachectl", "-D", "FOREGROUND"]
