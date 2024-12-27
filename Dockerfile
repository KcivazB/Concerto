# Étape 1 : Construction des assets (CSS/JS avec Node.js)
FROM node:current-slim AS assets_build

WORKDIR /app

# Copier uniquement les fichiers nécessaires pour npm/yarn
COPY package.json package-lock.json ./

# Installer les dépendances frontales
RUN npm install --silent

# Copier les fichiers nécessaires pour les builds frontaux
COPY . .

# Construire les assets (remplacez par votre commande de build si différente)
RUN npm run build

# Étape 2 : Build Symfony Backend
FROM dunglas/frankenphp:1-php8.3 AS symfony_base

WORKDIR /app

VOLUME /app/var/

# Installation des dépendances système nécessaires
RUN apt-get update && apt-get install -y --no-install-recommends \
    acl \
    file \
    gettext \
    git \
    && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP nécessaires
RUN set -eux; \
    install-php-extensions \
        @composer \
        apcu \
        intl \
        opcache \
        zip;

# Configuration de Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

# Copier uniquement les fichiers nécessaires pour composer
COPY --link composer.* symfony.* ./ 

# Installer les dépendances PHP (production uniquement)
RUN composer install --no-dev --prefer-dist --no-scripts --no-progress

# Copier les fichiers sources Symfony
COPY --link . ./

# Copier les assets générés depuis l'étape 1
COPY --from=assets_build /app/public/build ./public/build/

# Optimisation pour la production
RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer dump-env prod; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; \
    sync;

# Ajout des configurations spécifiques
COPY --link .docker/frankenphp/conf.d/10-app.ini $PHP_INI_DIR/app.conf.d/
COPY --link --chmod=755 .docker/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link .docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Configuration du point d'entrée
ENTRYPOINT ["docker-entrypoint"]

# Configuration de la vérification de santé
HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1

# Commande par défaut
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]
