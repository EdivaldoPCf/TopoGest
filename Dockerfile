# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS frontend

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

ARG VITE_APP_NAME=TopoGest
ARG VITE_REVERB_APP_KEY=topogest
ENV VITE_APP_NAME=${VITE_APP_NAME}
ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY}

RUN npm run build

FROM php:8.3-fpm-bookworm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        xml \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

COPY . .
COPY --from=frontend /build/public/build ./public/build

RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && php artisan package:discover --ansi \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/production.ini /usr/local/etc/php/conf.d/99-topogest.ini

EXPOSE 9000
CMD ["php-fpm"]

FROM app AS test

RUN composer install \
        --no-interaction \
        --no-progress \
        --optimize-autoloader \
        --prefer-dist \
    && touch .env

CMD ["php", "artisan", "test"]

FROM nginx:1.28-alpine AS web

COPY --from=app /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
