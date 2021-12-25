
ARG PHP_VERSION
ARG PHP_ALPINE_VERSION

FROM php:${PHP_VERSION:+${PHP_VERSION}-}alpine${PHP_ALPINE_VERSION} AS php

ENV PROJECT_ROOT=/php

RUN set -eux; \
    apk add --no-cache \
      bash \
    ; \
    adduser -u 1000 -DSh /php php php

ARG EXT_INSTALL
ARG EXT_ENABLE=''
RUN --mount=from=mlocati/php-extension-installer,dst=/build/extension-installer,src=/usr/bin/install-php-extensions \
    set -eux; \
    /build/extension-installer \
        opcache \
        ${EXT_INSTALL:-${EXT_ENABLE}} \
    ; \
    docker-php-ext-enable \
        opcache \
        ${EXT_ENABLE} \
    ;

RUN set eux; \
    mkdir -p /usr/local/lib/php/vendor; \
    echo "<?php file_exists('/php/vendor/autoload.php') and require '/php/vendor/autoload.php';" > /usr/local/lib/php/vendor/autoload.php; \
    echo 'auto_prepend_file=vendor/autoload.php' > "$PHP_INI_DIR/conf.d/99-autoload.ini"

USER php
WORKDIR /php
