FROM php:8.2-fpm

# Dépendances système nécessaires aux extensions PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
        intl \
        zip \
        ctype \
        iconv \
        pdo \
    && rm -rf /var/lib/apt/lists/*

# Extension Redis (client PHP)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier le projet (composer.json inclus)
COPY . .