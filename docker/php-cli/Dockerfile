ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions redis pdo_pgsql xdebug
