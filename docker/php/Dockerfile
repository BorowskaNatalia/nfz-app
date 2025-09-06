FROM php:8.3-fpm-alpine

# System deps
RUN apk add --no-cache \
    git curl zip unzip icu-libs icu-data-full oniguruma libpng libjpeg-turbo \
    libzip-dev icu-dev oniguruma-dev libpng-dev autoconf g++ make

# PHP extensions (pdo_mysql, zip, intl, opcache)
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql intl zip opcache

# Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Uprawnienia do cache/logów
RUN chown -R www-data:www-data /var/www/html || true
