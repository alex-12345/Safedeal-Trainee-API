ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm-alpine
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions redis pdo_pgsql opcache
