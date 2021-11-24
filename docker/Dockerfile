
FROM php:7.4-cli-alpine as php_build

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions; \
    apk add --update binutils; \
    install-php-extensions \
        ast-1.0.14 \
        xdebug \
        zip \
        pcntl \
        grpc \
        @composer \
    ; \
    # strip debug symbols from extensions to reduce size
    strip --strip-debug /usr/local/lib/php/extensions/no-debug-non-zts-20190902/grpc.so; \
    strip --strip-debug /usr/local/lib/php/extensions/no-debug-non-zts-20190902/xdebug.so;

FROM php_build

WORKDIR /usr/src/myapp

RUN   apk add --no-cache bash git; \
      chmod +x -R /usr/local/lib/php/extensions/no-debug-non-zts-20190902; \
      addgroup -g "1000" -S php; \
      adduser --system \
        --gecos "" \
        --ingroup "php" \
        --uid "1000" \
        "php";

USER php

