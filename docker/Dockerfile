ARG PHP_VERSION=8.3

FROM php:8.1.33-cli AS php-8.1-cli
FROM php:8.2.29-cli AS php-8.2-cli
FROM php:8.3.23-cli AS php-8.3-cli
FROM php:8.4.10-cli AS php-8.4-cli

FROM php-${PHP_VERSION}-cli

WORKDIR /usr/src/myapp

RUN apt-get update  \
    && apt-get install -y --no-install-recommends git \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd --gid 1000 php \
    && useradd --system --uid 1000 --gid php --shell /bin/bash --create-home php

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        @composer \
        ast \
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

USER php
