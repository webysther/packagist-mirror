FROM webysther/composer-debian

ARG PUID=1000
ARG PGID=1000

ENV PUID ${PUID}
ENV PGID ${PGID}
ENV TZ=UTC
ENV SLEEP=0

RUN groupadd -g ${PGID} packagist \
    && useradd -u ${PUID} -g packagist -m packagist -G docker_env \
    && usermod -p "*" packagist -s /bin/bash \
    && chown -R packagist:packagist /home/packagist \
    && echo "" >> ~/.bashrc \
    && echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.bashrc \
    && ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

USER packagist

RUN echo "" >> ~/.bashrc \
    && echo 'export PATH="~/.composer/vendor/bin:$PATH"' >> ~/.bashrc \
    && echo "" >> ~/.bashrc && echo 'export PATH="/var/www/vendor/bin:$PATH"' >> ~/.bashrc

USER root

COPY ./crontab /etc/cron.d

RUN chmod -R 644 /etc/cron.d \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog

ADD sync.sh /home/root/sync.sh

WORKDIR /var/www
