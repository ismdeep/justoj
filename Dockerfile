FROM ubuntu:20.04

MAINTAINER L. Jiang <l.jiang.1024@gmail.com>

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y apt-utils && \
    apt-get install -y htop xz-utils wget curl git locales vim zip gcc g++ make cmake unzip && \
    apt-get install -y zlibc zlib1g-dev nginx && \
    apt-get install -y php-fpm php-mysql php-zip php-xml php-gd php-mbstring php-redis && \
    apt-get install -y supervisor && \
    cd /root && \
    curl -sS https://getcomposer.org/installer -o composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    locale-gen en_US.UTF-8 && \
    echo "\
[supervisord]\n\
nodaemon=true\n\
\n\
[program:php-fpm]\n\
command=/etc/init.d/php7.4-fpm start\n\
\n\
[program:nginx]\n\
command=nginx" > /etc/supervisord.conf

WORKDIR /var/www
COPY . .
COPY .data/nginx-config /etc/nginx/sites-enabled/default
RUN set -eux; \
    rm -rfv .git; \
    mkdir runtime; \
    chmod -R 777 runtime; \
    composer install
EXPOSE 80
CMD ["/usr/bin/supervisord"]
