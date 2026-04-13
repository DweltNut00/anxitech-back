FROM php:8.2-apache

# 1. Dependencias del sistema (necesarias para gd y zip)
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

# 3. Habilitar mod_rewrite
RUN a2enmod rewrite

# 4. Permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 5. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 6. Copiar composer.json primero (caché de Docker)
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 7. Copiar el resto del proyecto
COPY . /var/www/html/

EXPOSE 80