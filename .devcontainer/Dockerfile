ARG PHP_VERSION=8.5@sha256:94b5f4681adde760369e8a302ad0ceb577123ac799bb4a701e2473137107efe5
FROM ghcr.io/open-telemetry/opentelemetry-php/opentelemetry-php-base:${PHP_VERSION}

USER root

# The base image has no ssh client. Git needs `ssh-keygen` to create SSH-format commit
# signatures (gpg.format=ssh) and `ssh` to push over git@github.com, so both would fail
# for anyone whose git config signs commits.
RUN apt-get update \
    && apt-get install -y --no-install-recommends openssh-client \
    && rm -rf /var/lib/apt/lists/*

# Docker creates a missing bind/volume mount point as root, which would leave the
# dependency volumes unwritable by the `php` user. Pre-creating them here means each
# empty volume inherits this ownership instead.
RUN mkdir -p \
      /usr/src/myapp/vendor \
      /usr/src/myapp/vendor-bin/deptrac/vendor \
      /usr/src/myapp/vendor-bin/infection/vendor \
      /usr/src/myapp/vendor-bin/phan/vendor \
      /usr/src/myapp/vendor-bin/php-cs-fixer/vendor \
      /usr/src/myapp/vendor-bin/phpbench/vendor \
      /usr/src/myapp/vendor-bin/phpmetrics/vendor \
      /usr/src/myapp/vendor-bin/psalm/vendor \
      /usr/src/myapp/vendor-bin/rector/vendor \
      /home/php/.cache/composer \
    && chown -R php:php /usr/src/myapp /home/php/.cache

USER php
