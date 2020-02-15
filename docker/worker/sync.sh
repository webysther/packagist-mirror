#!/bin/bash
set -e

pushd /var/www
    if [ -z "vendor" ]; then
        composer install --no-ansi --no-dev --optimize-autoloader
    fi
    while sleep $SLEEP; do php bin/mirror create --no-progress; done
popd

/bin/bash
