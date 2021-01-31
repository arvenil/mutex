FROM php:8-cli-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    memcache \
    memcached \
    pdo_mysql \
    pcntl \
    redis \
    xdebug
