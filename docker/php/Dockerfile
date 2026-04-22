FROM php:8.3-fpm-alpine

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
    && apk add --no-cache \
        libpq \
        libzip \
        icu-libs \
        oniguruma \
        git \
        unzip \
        nodejs \
        npm \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        bcmath \
        pcntl \
        intl \
        opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
