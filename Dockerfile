FROM php:7.2-buster
RUN apt-get update && apt-get -y install git zip \
&& curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer \
&& chmod +x /usr/local/bin/composer \
&& pecl channel-update pecl.php.net \
&& pecl install apcu
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
RUN composer install
ENTRYPOINT ["./vendor/phpunit/phpunit/phpunit", "--colors=always"]
