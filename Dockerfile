# Utilisation de l'image PHP avec FPM
FROM php:8.1-fpm

# Installation des dépendances PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip gd pdo pdo_mysql

# Installation de Composer (gestionnaire de dépendances PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installation des dépendances Symfony
WORKDIR /var/www
COPY . /var/www

# Déclaration des ARG (en cas de valeurs par défaut à définir)
ARG APP_ENV=prod
ARG APP_SECRET=your_default_secret
ARG DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8&charset=utf8mb4"
ARG MESSENGER_TRANSPORT_DSN="doctrine://default?auto_setup=0"
ARG MAILER_DSN="smtp://address:password@host:port"
ARG OAUTH_GOOGLE_CLIENT_ID=YOUR_CLIENT_ID
ARG OAUTH_GOOGLE_CLIENT_SECRET=YOUR_CLIENT_SECRET
ARG OAUTH_GITHUB_CLIENT_ID=YOUR_CLIENT_ID
ARG OAUTH_GITHUB_CLIENT_SECRET=YOUR_CLIENT_SECRET
ARG DISCORD_CLIENT_ID=YOUR_CLIENT_ID
ARG DISCORD_CLIENT_SECRET=YOUR_CLIENT_SECRET

# Définir les ENV via les ARG
ENV APP_ENV=${APP_ENV}
ENV APP_SECRET=${APP_SECRET}
ENV DATABASE_URL=${DATABASE_URL}
ENV MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN}
ENV MAILER_DSN=${MAILER_DSN}
ENV OAUTH_GOOGLE_CLIENT_ID=${OAUTH_GOOGLE_CLIENT_ID}
ENV OAUTH_GOOGLE_CLIENT_SECRET=${OAUTH_GOOGLE_CLIENT_SECRET}
ENV OAUTH_GITHUB_CLIENT_ID=${OAUTH_GITHUB_CLIENT_ID}
ENV OAUTH_GITHUB_CLIENT_SECRET=${OAUTH_GITHUB_CLIENT_SECRET}
ENV DISCORD_CLIENT_ID=${DISCORD_CLIENT_ID}
ENV DISCORD_CLIENT_SECRET=${DISCORD_CLIENT_SECRET}

# Créer le fichier .env avec les variables d'environnement
RUN echo "APP_ENV=${APP_ENV}" > /var/www/.env && \
    echo "APP_SECRET=${APP_SECRET}" >> /var/www/.env && \
    echo "DATABASE_URL=${DATABASE_URL}" >> /var/www/.env && \
    echo "MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN}" >> /var/www/.env && \
    echo "MAILER_DSN=${MAILER_DSN}" >> /var/www/.env && \
    echo "OAUTH_GOOGLE_CLIENT_ID=${OAUTH_GOOGLE_CLIENT_ID}" >> /var/www/.env && \
    echo "OAUTH_GOOGLE_CLIENT_SECRET=${OAUTH_GOOGLE_CLIENT_SECRET}" >> /var/www/.env && \
    echo "OAUTH_GITHUB_CLIENT_ID=${OAUTH_GITHUB_CLIENT_ID}" >> /var/www/.env && \
    echo "OAUTH_GITHUB_CLIENT_SECRET=${OAUTH_GITHUB_CLIENT_SECRET}" >> /var/www/.env && \
    echo "DISCORD_CLIENT_ID=${DISCORD_CLIENT_ID}" >> /var/www/.env && \
    echo "DISCORD_CLIENT_SECRET=${DISCORD_CLIENT_SECRET}" >> /var/www/.env

# Installation de Composer et des dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Installation de Node.js pour les assets si nécessaire
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash \
    && apt-get install -y nodejs \
    && npm install

# Définition des variables d'environnement pour Symfony
ENV SYMFONY_ENV=prod

# Expose le port 9000 pour PHP-FPM
EXPOSE 9000

# Commande pour démarrer PHP-FPM
CMD ["php-fpm"]
