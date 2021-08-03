#!/usr/bin/env bash

apt-get update
apt-get install -y php-curl
cd /var/www/justoj && composer install
mkdir -p /var/www/justoj/runtime
chmod -R 777 /var/www/justoj/runtime
