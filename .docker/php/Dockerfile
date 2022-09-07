FROM php:8.1-cli-alpine

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/xdebug.ini
