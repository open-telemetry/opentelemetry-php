FROM php:7.4-cli-alpine

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions; \
    apk add --no-cache bash git binutils; \
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
    strip --strip-debug /usr/local/lib/php/extensions/no-debug-non-zts-20190902/xdebug.so; \
    rm /usr/local/bin/install-php-extensions; \
    apk del --no-cache binutils

USER guest

WORKDIR /usr/src/myapp
