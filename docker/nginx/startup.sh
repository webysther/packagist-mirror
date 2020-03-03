#!/bin/bash

crond -l 2 -b
nginx -t
nginx
