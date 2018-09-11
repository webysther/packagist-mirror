#!/usr/bin/env bash
# by klzsysy

set -e

if [ "$#" -ne 0 ];then
    if which $1 > /dev/null ;then
        exec "$@"
        exit $?
    fi
fi

SYNC_INTERVAL_DAY=${SYNC_INTERVAL_DAY:='360'}

WEEK_SYNC_TIME=${WEEK_SYNC_TIME:='all'}
SERVER_NAME=${SERVER_NAME:-'http://localhost'}

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
        kill -s SIGTERM "${sleep_pid}"
        kill -s SIGTERM "$syncpid"
        wait "$syncpid"
        exit $?
}

trap 'handle_TERM' SIGTERM


nginx -t && nginx

composersync(){
    info "start sync ....."
    exec php bin/mirror create ${DEBUG}  &
    syncpid=$!
    wait $syncpid
    info "sync end"
}


sed -i "s#SERVER_NAME#${SERVER_NAME}#g" index.html
cp -f index.html public/index.html


while true;
do
    if echo "${WEEK_SYNC_TIME}" | grep -q "$(date '+%u')" ;then
        composersync $@
        sleep $(( ${SYNC_INTERVAL_DAY} * 60 )) &
        sleep_pid=$!
        wait ${sleep_pid}
    else
        sleep 60 &
        sleep_pid=$!
        wait ${sleep_pid}
    fi
done
