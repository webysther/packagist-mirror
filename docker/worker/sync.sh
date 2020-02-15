#!/bin/bash
set -e

pushd /var/www
    composer install --no-progress --no-ansi --no-dev --optimize-autoloader
    while sleep $SLEEP; do php bin/mirror create --no-progress; done
popd

/bin/bash
