FROM php:7.3-fpm-alpine

RUN apk update && apk add --no-cache \
    $PHPIZE_DEPS

RUN pecl channel-update pecl.php.net && \
    pecl install redis \
    && docker-php-ext-enable redis

# Clean
RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*
