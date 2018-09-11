#!/usr/bin/env bash
# by klzsysy

set -e

if [ "$#" -ne 0 ];then
    if which $1 > /dev/null ;then
        exec "$@"
        exit $?
    fi
fi

SYNC_INTERVAL=${SYNC_INTERVAL:='360'}

WEEK_SYNC_TIME=${WEEK_SYNC_TIME:='all'}
SERVER_NAME=${SERVER_NAME:-'http://localhost'}
SLEEP=$(( ${SYNC_INTERVAL} * 60 ))
APP_COUNTRY_NAME=${APP_COUNTRY_NAME:-'China'}
APP_COUNTRY_CODE=${APP_COUNTRY_CODE:-'cn'}


if [ -z "${DEBUG}" ];then
    DEBUG='--no-progress'
fi

if [ "${WEEK_SYNC_TIME}" == 'all' ];then
    WEEK_SYNC_TIME=$(seq 1 7)
fi

if [ -n "${HTTP_PORT}" ];then
    sed -i "s/8080/${HTTP_PORT}/" /etc/nginx/conf.d/nginx-site.conf
fi

function info(){
    echo "$(date '+%F %T') - info: $@"
}

function handle_TERM()
{
        kill -s SIGTERM $(ps aux | grep -v grep| grep  'nginx: master' | awk '{print $2}')
        kill -s SIGTERM $(ps aux | grep -v grep| grep  'php-fpm: master' | awk '{print $2}')
        kill -s SIGTERM "${proxy_pid}"
        kill -s SIGTERM "${sleep_pid}"
        kill -s SIGTERM "$sync_pid"
        wait "$sync_pid"
        exit $?
}




function init_var(){
    sed -i "s#location /proxy#location /${URL_PREFIX}#" nginx-site.conf
    cp -r nginx-site.conf /etc/nginx/conf.d/

    sed -i "s#SERVER_NAME#${SERVER_NAME}#g"                 index.html
    cp -f index.html public/index.html
}
init_var
trap 'handle_TERM' SIGTERM


nginx -t && nginx
python3 ./proxy.py &
proxy_pid=$!

composersync(){
    info "start sync ....."
    exec php bin/mirror create ${DEBUG}  &
    sync_pid=$!
    wait $sync_pid
    info "sync end"
}


while true;
do
    if echo "${WEEK_SYNC_TIME}" | grep -q "$(date '+%u')" ;then
        composersync $@
        sleep $(( ${SYNC_INTERVAL} * 60 )) &
        sleep_pid=$!
        wait ${sleep_pid}
    else
        sleep 60 &
        sleep_pid=$!
        wait ${sleep_pid}
    fi
done
