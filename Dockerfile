FROM ubuntu:xenial

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get -y update && apt-get -y upgrade

RUN apt install -y --force-yes php php-fpm php-cli php-mcrypt php-mysql php-intl php-xml php-redis php-curl
RUN apt install -y --force-yes php-xdebug
RUN echo "xdebug.remote_enable=1" >> /etc/php/7.0/fpm/conf.d/20-xdebug.ini \
 && echo "xdebug.remote_autostart=0" >> /etc/php/7.0/fpm/conf.d/20-xdebug.ini \
 && echo "xdebug.remote_connect_back=1" >> /etc/php/7.0/fpm/conf.d/20-xdebug.ini \
 && echo "xdebug.idekey=PHPSTORM" >> /etc/php/7.0/fpm/conf.d/20-xdebug.ini
RUN apt install -y --force-yes nginx

RUN mkdir -p /srv/games
RUN mkdir -p /var/run/php

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log && ln -sf /dev/stderr /var/log/nginx/error.log

# permissions fix
RUN usermod -u 1000 www-data

# http ssl xdebug
EXPOSE 80 443 9000

CMD ["sh", "-c", "php-fpm7.0; nginx -g 'daemon off;'"]