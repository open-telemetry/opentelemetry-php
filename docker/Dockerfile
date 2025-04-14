ARG PHP_VERSION=8.0

FROM php:8.4.6-cli-alpine AS php-8.0-cli-alpine
FROM php:8.4.6-cli-alpine AS php-8.1-cli-alpine
FROM php:8.4.6-cli-alpine AS php-8.2-cli-alpine
FROM php:8.4.6-cli-alpine AS php-8.3-cli-alpine
FROM php:8.4.6-cli-alpine AS php-8.4-cli-alpine

FROM php-${PHP_VERSION}-cli-alpine

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
    && apk add binutils \
    && install-php-extensions \
        @composer \
        ast \
        grpc \
        intl\
        opcache \
        opentelemetry \
        pcntl \
        protobuf \
        sockets \
        xdebug \
        zip \
    && find /usr/local/lib/php/extensions -name "*.so" -exec strip --strip-debug {} \;

RUN  echo "grpc.enable_fork_support = 1" > $(php-config --ini-dir)/grpc.ini \
  && echo "grpc.poll_strategy = epoll1" >> $(php-config --ini-dir)/grpc.ini

WORKDIR /usr/src/myapp

RUN   apk add --no-cache bash git; \
      find /usr/local/lib/php/extensions -type d -exec chmod +x -R {} \;; \
      addgroup -g "1000" -S php; \
      adduser --system \
        --gecos "" \
        --ingroup "php" \
        --uid "1000" \
        "php";

USER php
