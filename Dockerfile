FROM php:8.2-cli

# Installe les dépendances système
RUN apt-get update && apt-get install -y \
    unzip git zip libicu-dev libpq-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définit le dossier de travail
WORKDIR /app

# Copie le projet
COPY . .

# Installe les dépendances PHP
RUN composer install --no-interaction --no-dev --optimize-autoloader --no-scripts

# Port exposé
EXPOSE 10000

# Commande de démarrage
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
