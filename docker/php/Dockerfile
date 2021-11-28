FROM composer as builder
COPY ./app/composer.* ./
RUN composer install

FROM php:7.4-fpm-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=builder /app/vendor /var/www/html/vendor

RUN apk add icu-dev git vim less

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN docker-php-ext-enable intl