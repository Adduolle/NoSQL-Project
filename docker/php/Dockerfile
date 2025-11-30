FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libicu-dev libpq-dev \
    && docker-php-ext-install intl zip

# Extension Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY storyteller/composer.json storyteller/composer.lock ./

RUN composer install --no-scripts --no-interaction
COPY storyteller/. .
RUN composer dump-autoload --optimize
RUN composer require neo4j/neo4j-bundle --no-interaction
RUN composer require predis/predis