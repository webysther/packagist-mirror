#! /bin/bash

LOCK_FILE=/tmp/php_mirror.lock
PHP_BIN=/usr/bin/php
MIRROR_PATH=/data/packagist-mirror
MIRROR_PROXY_PATH=/data/packagist-mirror-proxy

if [ ! -f $LOCK_FILE ]; then
	echo $$ > $LOCK_FILE
	$PHP_BIN $MIRROR_PATH/bin/mirror create
	rsync -avz --recursive --stats $MIRROR_PATH/ cnpkg@cnpkg-wx:$MIRROR_PATH

	$MIRROR_PATH/rehash \
	    -from $MIRROR_PATH/public/ \
	    -target $MIRROR_PROXY_PATH/public/

	cp -f $MIRROR_PATH/public/index.html $MIRROR_PROXY_PATH/public/index.html
	rsync --exclude="public/hashed.log" -avz --recursive --stats $MIRROR_PROXY_PATH/ cnpkg@cnpkg-wx:$MIRROR_PROXY_PATH
    
    rm -rf $LOCK_FILE
else
	pid=`cat $LOCK_FILE`
	echo "Running: $pid"
fi
