FROM php:8.3-fpm-alpine

# Instalar dependencias de build, compilar extensiones, y limpiar en la misma capa.
# Las runtime-deps quedan; las build-deps se borran al final.
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
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        bcmath \
        pcntl \
        intl \
        opcache \
    && apk del .build-deps

# Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN adduser -D -u 1000 laravel && \
    chown -R laravel:laravel /var/www/html

USER laravel
