FROM ismdeep/nginx-php:ubuntu-20-04

MAINTAINER L. Jiang <l.jiang.1024@gmail.com>

COPY . /var/www/justoj
COPY nginx-config /etc/nginx/sites-enabled/justoj
RUN cd /var/www/justoj && \
    rm -rfv configs; \
    rm -rfv runtime; \
    rm -rfv vendor; \
    rm Dockerfile; \
    rm nginx-config; \
    mkdir runtime; chmod -R 777 runtime
RUN cd /var/www/justoj && composer install