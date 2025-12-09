FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libicu-dev libpq-dev \
    && docker-php-ext-install intl zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY storyteller/ ./

RUN composer require \
    symfony/security-bundle \
    neo4j/neo4j-bundle \
    symfony/orm-pack \
    doctrine/doctrine-migrations-bundle \
    laudis/neo4j-php-client \
    --no-interaction --no-scripts

RUN composer install --no-interaction --optimize-autoloader --no-scripts

RUN composer dump-autoload --optimize
