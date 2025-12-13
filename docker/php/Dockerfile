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

# Copier le projet
COPY storyteller/ ./

# Installer les dépendances PHP sans exécuter les scripts Symfony auto
RUN composer require \
    symfony/security-bundle \
    laudis/neo4j-php-client \
    --no-interaction --no-scripts

# Installer toutes les dépendances depuis composer.lock
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Optimiser l’autoload
RUN composer dump-autoload --optimize
