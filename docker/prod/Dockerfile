# Imagen de produccion: PHP-FPM + Nginx en un solo contenedor
# Usada para Azure Container Apps

# --- Stage 1: instalar dependencias PHP y compilar assets ---
FROM php:8.3-fpm-alpine AS builder

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        postgresql-dev libzip-dev oniguruma-dev icu-dev \
    && apk add --no-cache \
        libpq libzip icu-libs oniguruma git unzip nodejs npm \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql zip bcmath pcntl intl opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY app/composer.json app/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY app/ .
RUN mkdir -p bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-scripts \
    && php artisan package:discover --ansi \
    && npm ci && npm run build && rm -rf node_modules

# --- Stage 2: imagen final ligera ---
FROM php:8.3-fpm-alpine

RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        postgresql-dev libzip-dev oniguruma-dev icu-dev \
    && apk add --no-cache \
        libpq libzip icu-libs oniguruma nginx supervisor \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql zip bcmath pcntl intl opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Configuracion de Nginx
COPY docker/prod/nginx.conf /etc/nginx/http.d/default.conf

# Configuracion de Supervisor (php-fpm + nginx)
COPY docker/prod/supervisord.conf /etc/supervisord.conf

# Opcache para produccion
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-prod.ini

WORKDIR /var/www/html
COPY --from=builder /var/www/html .

# Permisos para storage y cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
