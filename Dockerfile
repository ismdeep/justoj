FROM ismdeep/nginx-php:ubuntu-20-04

MAINTAINER L. Jiang <l.jiang.1024@gmail.com>

RUN mkdir -p       /var/www/justoj
COPY application   /var/www/justoj/
COPY extend        /var/www/justoj/
COPY public        /var/www/justoj/
COPY composer.json /var/www/justoj/
COPY composer.lock /var/www/justoj/
COPY think         /var/www/justoj/

COPY nginx-config /etc/nginx/sites-enabled/justoj
RUN cd /var/www/justoj; mkdir runtime; chmod -R 777 runtime
RUN cd /var/www/justoj && composer install