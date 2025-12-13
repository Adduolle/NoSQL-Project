FROM php:8.2-fpm

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libicu-dev libpq-dev \
    && docker-php-ext-install intl zip

# Installer Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

COPY storyteller/composer.json ./

# Installer toutes les dépendances exactement comme dans composer.lock
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN composer require symfony/asset:^7.3

# Copier le projet
COPY storyteller/ ./