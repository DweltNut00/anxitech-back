FROM php:8.2-cli

# 1. Dependencias del sistema
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

# 3. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 4. Copiar composer.json primero
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 5. Copiar el resto del proyecto
COPY . /var/www/html/

EXPOSE 80

# Servidor built-in de PHP — sin Apache, sin nginx
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]