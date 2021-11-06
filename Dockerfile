FROM ismdeep/nginx-php:ubuntu-20-04

MAINTAINER L. Jiang <l.jiang.1024@gmail.com>

WORKDIR /var/www
COPY . .
COPY nginx-config /etc/nginx/sites-enabled/default
RUN git describe --abbrev=0 --tags > /justoj-version && \
    rm -rfv .git && \
    mkdir runtime && \
    chmod -R 777 runtime && \
    composer install