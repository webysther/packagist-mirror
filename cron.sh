#! /bin/bash

LOCK_FILE=/tmp/php_mirror.lock
PHP_BIN=/usr/bin/php
MIRROR_PATH=/data/packagist-mirror

if [ ! -f $LOCK_FILE ]; then
	echo $$ > $LOCK_FILE
	$PHP_BIN $MIRROR_PATH/bin/mirror create
	rsync -avz --recursive --stats $MIRROR_PATH/ cnpkg@cnpkg-wx:$MIRROR_PATH
	rm -rf $LOCK_FILE
else
	pid=`cat $LOCK_FILE`
	echo "Running: $pid"
fi