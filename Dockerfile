FROM php:8.2-fpm

# Installation des dépendances système nécessaires pour PostgreSQL et la gestion des images
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip

# Nettoyage du cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP requises par Laravel et PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql gd bcmath

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY . /var/www

# Installation des dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Ajustement des permissions pour les dossiers de stockage et de cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exposition du port
EXPOSE 8000

# Commande de démarrage avec migration automatique de la base de données
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
