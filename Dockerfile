# Build stage for Symfony backend
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

# Copier tout le reste des fichiers sources Symfony
COPY --link . ./

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
