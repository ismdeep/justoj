#!/usr/bin/env bash

set -eux

apt-get update
apt-get upgrade -y
apt-get install -y apt-utils
apt-get install -y htop xz-utils wget curl git locales vim zip
apt-get install -y gcc g++ make cmake unzip zlibc zlib1g-dev
apt-get install -y php-fpm php-mysql php-zip php-xml php-gd php-mbstring php-redis
apt-get install -y supervisor

# Install composer
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php

locale-gen en_US.UTF-8

sed -i 's/listen\ =\ \/run\/php\/php7\.4-fpm\.sock/listen\ =\ 0\.0\.0\.0\:9000/g' /etc/php/7.4/fpm/pool.d/www.conf

# run in foreground
/usr/sbin/php-fpm7.4 -F
