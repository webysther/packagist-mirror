FROM composer
MAINTAINER Webysther Nunes <webysther@gmail.com>

WORKDIR /packagist

RUN git clone git@github.com:Webysther/mirror.git mirror
RUN cd mirror && composer install

VOLUME /public
RUN ln -s /packagist/mirror/public /public

CMD php /packagist/mirror/bin/mirror create
