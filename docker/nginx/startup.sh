#!/bin/bash

echo 'starting cron'
crond -l 2 -b

echo 'validating nginx'
nginx -t

echo 'starting nginx'
nginx
