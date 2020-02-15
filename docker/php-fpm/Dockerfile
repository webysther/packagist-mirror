FROM php:7.4-fpm-alpine

RUN echo http://dl-2.alpinelinux.org/alpine/edge/community/ >> /etc/apk/repositories \
    && apk add --no-cache --update --virtual buildDeps autoconf shadow \
    && usermod -u 1000 www-data \
    && docker-php-ext-install opcache

COPY ./opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY ./packagist.ini /usr/local/etc/php/conf.d

WORKDIR /var/www

USER www-data

CMD ["php-fpm"]

EXPOSE 9000
