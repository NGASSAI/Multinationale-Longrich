FROM php:8.4-fpm

# Installation des dépendances système requises pour Composer et PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP requises par Laravel
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd bcmath zip

# Copie de l'exécutable Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du dossier de travail
WORKDIR /var/www

# Copie de l'intégralité du projet
COPY . .

# Installation des dépendances Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajustement des permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
