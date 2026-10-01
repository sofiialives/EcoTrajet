# ---------- Stage 1 : builder (création + dépendances) ----------
FROM php:8.2-cli-alpine AS builder

ARG APP_NAME=symfony-app
ENV COMPOSER_ALLOW_SUPERUSER=1

# Dépendances nécessaires pour Symfony + extensions PHP
RUN apk add --no-cache \
    curl git unzip bash \
    icu-dev libzip-dev oniguruma-dev sqlite-dev libxml2-dev \
 && docker-php-ext-install \
    intl zip pdo_mysql pdo_sqlite mbstring xml

# Composer + Symfony CLI
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN curl -sS https://get.symfony.com/cli/installer | bash \
 && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Création d’un skeleton Symfony réutilisable comme "template"
WORKDIR /opt
RUN symfony new --webapp "${APP_NAME}" --no-git

WORKDIR /opt/${APP_NAME}
RUN composer install --no-interaction --prefer-dist --no-progress

# Droits pour que www-data puisse bosser dessus
RUN mkdir -p var && chown -R www-data:www-data .

# ---------- Stage 2 : runtime ----------
FROM php:8.2-cli-alpine AS runtime

ARG APP_NAME=symfony-app
ENV APP_ENV=dev \
    APP_DEBUG=1 \
    COMPOSER_ALLOW_SUPERUSER=1

# Libs runtime uniquement
RUN apk add --no-cache \
    bash curl icu-libs libzip sqlite-libs libxml2

# Récupérer les extensions et conf PHP construites dans le builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Composer + Symfony CLI
COPY --from=builder /usr/bin/composer /usr/bin/composer
COPY --from=builder /usr/local/bin/symfony /usr/local/bin/symfony

# Template Symfony pré-généré
COPY --from=builder /opt/${APP_NAME}/ /opt/app-template/

# Config PHP perso (à créer à côté du Dockerfile)
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www
EXPOSE 8000

CMD ["/bin/sh","-lc", "\
  if [ ! -f /var/www/composer.json ]; then \
    echo '[init] Copie du template Symfony vers /var/www'; \
    cp -a /opt/app-template/. /var/www && chown -R www-data:www-data /var/www; \
  fi; \
  cd /var/www; \
  if [ -f composer.json ] && [ ! -d vendor ]; then \
    echo '[composer] installation des dépendances'; \
    composer install --no-interaction --prefer-dist --no-progress; \
  fi; \
  exec symfony server:start --no-tls --document-root=public --allow-all-ip --port=8000 \
"]
