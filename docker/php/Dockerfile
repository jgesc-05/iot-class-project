FROM php:8.3-fpm-alpine

# Dependencias del sistema
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    git \
    unzip

# Extensiones PHP necesarias para Laravel + PostgreSQL
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    pcntl \
    intl \
    opcache

# Composer (gestor de paquetes de PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo donde vivirá la app
WORKDIR /var/www/html

# Usuario no-root para seguridad (opcional pero buena práctica)
RUN adduser -D -u 1000 laravel && \
    chown -R laravel:laravel /var/www/html

USER laravel
