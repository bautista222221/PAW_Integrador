# Imagen base con Apache y PHP
FROM php:8.4-cli

WORKDIR /var/www/html

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpq-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copiamos el código al contenedor
COPY . .

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install

EXPOSE 8000

CMD ["php", "-d", "upload_max_filesize=100M", "-d", "post_max_size=100M", "-S", "0.0.0.0:8000", "-t", "public"]
