FROM php:7.2


RUN apt-get update && \
    rm /etc/apt/preferences.d/no-debian-php && \
    apt-get  install -y nginx git procps zip unzip vim && \
    apt-get clean && \
    rm -rf /var/cache/apt

WORKDIR /repo
ENV TZ=Asia/Shanghai DATA=/repo/public


RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/bin/ --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    chmod a+x /usr/bin

ADD composer.json ./

RUN composer install

COPY . .

RUN useradd -u 10000 -G root repo && \
    mkdir -p ${DATA}  /var/nginx && \
	ln -sf /dev/stdout /var/log/nginx/access.log && \
	ln -sf /dev/stderr /var/log/nginx/error.log && \
    cp -f nginx.conf /etc/nginx/nginx.conf && \
    cp nginx-site.conf /etc/nginx/conf.d/ && \
	chmod 2775 -R ${DATA} . /var/nginx /var/log /var/run && \
    chown repo:root -R /var/lib/nginx/  /var/nginx . ${DATA} /etc/nginx/conf.d/ && \
    chmod -R 775  /etc/nginx/conf.d/ *.sh /var/lib/nginx/ /var/nginx

VOLUME [ "/repo/public" ]
EXPOSE 8080

STOPSIGNAL SIGTERM
USER repo

ENTRYPOINT [  "./entrypoint.sh" ]