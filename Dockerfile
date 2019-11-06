FROM php:7.2-buster
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer
RUN composer install
ENTRYPOINT ["./vendor/phpunit/phpunit/phpunit", "--colors=always"]
