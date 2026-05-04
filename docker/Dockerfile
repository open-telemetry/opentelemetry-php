FROM composer:2.9@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318 AS composer
FROM debian:trixie-20260421@sha256:35b8ff74ead4880f22090b617372daff0ccae742eb5674455d542bef71ef1999

ARG PHP_VERSION=8.5

WORKDIR /usr/src/myapp

RUN apt-get update \
    && apt-get install -y --no-install-recommends git wget gnupg2 \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd --gid 1000 php \
    && useradd --system --uid 1000 --gid php --shell /bin/bash --create-home php

# https://packages.sury.org/php/README.txt
RUN apt-get update \
    && apt-get -y install lsb-release ca-certificates curl \
    && curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb \
    && dpkg -i /tmp/debsuryorg-archive-keyring.deb \
    && rm /tmp/debsuryorg-archive-keyring.deb \
    && sh -c 'echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
    && apt-get update

RUN apt-get install -y \
    php${PHP_VERSION}-amqp \
    php${PHP_VERSION}-ast \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-grpc \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-mongodb \
    php${PHP_VERSION}-mysqli \
    `if echo "${PHP_VERSION}" | grep -qE '^8\.(1|2|3|4)'; then echo "php${PHP_VERSION}-opcache"; fi` \
    php${PHP_VERSION}-opentelemetry \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-protobuf \
    php${PHP_VERSION}-rdkafka \
    php${PHP_VERSION}-simplexml \
    php${PHP_VERSION}-sockets \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-xdebug \
    php${PHP_VERSION}-zip \
    unzip

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN echo "grpc.enable_fork_support = 1" > $(php-config --ini-dir)/40-otel-dev.ini \
  && echo "grpc.poll_strategy = epoll1" >> $(php-config --ini-dir)/40-otel-dev.ini \
  && echo "zend.assertions = 1" >> $(php-config --ini-dir)/40-otel-dev.ini

USER php
