#!/usr/bin/env bash

apt-get update
apt-get install -y php-curl
cd /var/www/justoj && composer install