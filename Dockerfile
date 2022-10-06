FROM ubuntu:20.04
MAINTAINER L. Jiang <l.jiang.1024@gmail.com>
ENV DEBIAN_FRONTEND=noninteractive
COPY . /src
RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y apt-utils; \
    apt-get install -y htop xz-utils wget curl git locales vim zip gcc g++ make cmake unzip; \
    apt-get install -y zlibc zlib1g-dev nginx; \
    apt-get install -y php-fpm php-mysql php-zip php-xml php-gd php-mbstring php-redis php-curl; \
    locale-gen en_US.UTF-8; \
    curl -sS https://getcomposer.org/installer -o composer-setup.php; \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer; \
    rm -f composer-setup.php; \
    cd /src; \
    mkdir runtime; \
    chmod -R 777 runtime; \
    composer install
COPY ./.data/docker/default.conf /etc/nginx/sites-enabled/default
COPY ./.data/docker/entrypoint.bash /entrypoint.bash
EXPOSE 80
ENTRYPOINT ["bash", "/entrypoint.bash"]
