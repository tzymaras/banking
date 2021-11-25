FROM composer as builder
COPY ./app/composer.* ./
RUN composer install

FROM php:7.4-fpm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=builder /app/vendor /var/www/html/vendor

RUN docker-php-ext-install mysqli pdo pdo_mysql