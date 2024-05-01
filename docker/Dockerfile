ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-cli-alpine

ENV PHIVE_HOME=/usr/src/myapp/var/phive
WORKDIR /usr/src/myapp
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN wget https://phar.io/releases/phive.phar \
    && chmod +x phive.phar \
    && mv phive.phar /usr/local/bin/phive

RUN chmod +x /usr/local/bin/install-php-extensions \
    && apk add \
      binutils \
      gpg \
    && install-php-extensions \
        ast \
        opcache \
        xdebug \
        zip \
        pcntl \
        grpc \
        protobuf \
        sockets \
        intl \
        @composer \
    && find /usr/local/lib/php/extensions -name "*.so" -exec strip --strip-debug {} \;

RUN   apk add --no-cache bash git; \
      find /usr/local/lib/php/extensions -type d -exec chmod +x -R {} \;; \
      addgroup -g "1000" -S php; \
      adduser --system \
        --gecos "" \
        --ingroup "php" \
        --uid "1000" \
        "php";


USER php
