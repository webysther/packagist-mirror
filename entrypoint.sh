#!/usr/bin/env bash
# by klzsysy

set -e

if [ "$#" -ne 0 ];then
    if which $1 > /dev/null ;then
        exec "$@"
        exit $?
    fi
fi

SYNC_INTERVAL=${SYNC_INTERVAL:='30'}

WEEK_SYNC_TIME=${WEEK_SYNC_TIME:='all'}
SERVER_URL=${SERVER_URL:-'http://localhost'}
SLEEP=$(( ${SYNC_INTERVAL} * 60 ))

PROXY_URL_PREFIX=${PROXY_URL_PREFIX:-'zipcache'}
EXTERNAL_PORT=${EXTERNAL_PORT:-"80"}
HTTP_PORT=${HTTP_PORT:-'8080'}
OPTION=${OPTION:-'--no-progress'}
MAIN_MIRROR=${MAIN_MIRROR:-'https://packagist.laravel-china.org'}


if [ "${WEEK_SYNC_TIME}" == 'all' ];then
    WEEK_SYNC_TIME=$(seq 1 7)
fi

if [ -n "${HTTP_PORT}" ];then
    sed -i "s/8080/${HTTP_PORT}/" /etc/nginx/conf.d/nginx-site.conf
fi

if [ "${OPTION}" == "debug" ];then
    OPTION=""
    set -x
fi

function info(){
    echo "$(date '+%F %T') - info: $@"
}

function handle_TERM()
{
        kill -s SIGTERM $(ps aux | grep -v grep| grep  'nginx: master' | awk '{print $2}')
        kill -s SIGTERM "${proxy_pid}"
        kill -s SIGTERM "${sleep_pid}"
        kill -s SIGTERM "${sync_pid}"
        wait "${sync_pid}"
        exit $?
}

function update_packages_json(){
    _SERVER_URL=$(echo "${SERVER_URL}" | sed 's#/#\\/#g')
    if [ "${EXTERNAL_PORT}" != "80" ];then
        _SERVER_URL="${_SERVER_URL}:${EXTERNAL_PORT}"
    fi
    _value="[{\"dist-url\":\"${_SERVER_URL}\/${PROXY_URL_PREFIX}\/%package%\/%reference%.%type%\",\"preferred\":true}]"
    gzip -cd public/packages.json.gz | jq ". += {\"mirrors\": ${_value}}" | gzip > /opt/share/packages.json.gz

}

function composersync(){
    info "start sync ....."
    php bin/mirror create ${OPTION} -vvv &
    sync_pid=$!
    wait ${sync_pid}
    update_packages_json
    info "sync end"

}

function init_var(){
    sed -i "s#location /proxy#location /${PROXY_URL_PREFIX}#" nginx-site.conf
    cp -r nginx-site.conf /etc/nginx/conf.d/
    cp -f index.html public/index.html
    sed -i "s#sleep=.*#SLEEP=${SLEEP}#" .env.example
    sed -i "s#MAIN_MIRROR=.*#MAIN_MIRROR=${MAIN_MIRROR}#" .env.example

    cd /opt/share
    ln -sf /repo/public/*.png .
    ln -sf /repo/public/*.ico .
    ln -sf /repo/public/*.txt .
    ln -sf /repo/public/*.html .
    ln -sf /repo/public/${PROXY_URL_PREFIX} .
    ln -sf /repo/public/p .
    ln -sf packages.json.gz packages.json
    cd - > /dev/null
}

init_var
trap 'handle_TERM' SIGTERM


nginx -t && nginx
python3 ./proxy.py &
proxy_pid=$!

set +e

while true;
do
    if echo "${WEEK_SYNC_TIME}" | grep -q "$(date '+%u')" ;then
        composersync $@
        sleep $(( ${SYNC_INTERVAL} * 60 )) &
        sleep_pid=$!
        wait ${sleep_pid}
    else
        sleep 50 &
        sleep_pid=$!
        wait ${sleep_pid}
    fi
done
