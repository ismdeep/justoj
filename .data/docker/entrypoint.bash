#!/usr/bin/env bash

set -eux

/etc/init.d/php7.4-fpm start

nginx -g 'daemon off;'
