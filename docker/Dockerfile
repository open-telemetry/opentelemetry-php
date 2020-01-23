FROM php:7.1-buster

RUN apt-get -y update && apt-get -y install git zip && \
curl -sS https://getcomposer.org/installer | php && \
mv composer.phar /usr/local/bin/composer && \
chmod +x /usr/local/bin/composer && \
pecl install ast-1.0.4 xdebug && \
docker-php-ext-enable ast xdebug && \
# The pcntl extension is used for speeding up `make phan`
docker-php-ext-install pcntl

WORKDIR /usr/src/myapp
