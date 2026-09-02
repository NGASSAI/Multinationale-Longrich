FROM php:8.2-fpm

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

# Copie préalable des fichiers de dépendances pour optimiser le cache Docker
COPY composer.json composer.lock* ./

# Copie de l'intégralité du projet
COPY . .

# Installation des dépendances Composer (sans interaction et en ignorant les contraintes de version de plateforme)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Ajustement des permissions pour les dossiers de stockage et de cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exposition du port
EXPOSE 8000

# Commande de démarrage
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
