FROM php:8.2-apache

# 1. Dependencias del sistema
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Extensiones PHP
RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

# 3. Forzar solo mpm_prefork — eliminar otros MPM directamente
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_worker.load && \
    a2enmod mpm_prefork rewrite headers

# 4. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 5. Copiar composer.json primero
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 6. Copiar el resto del proyecto
COPY . /var/www/html/

# 7. Permisos y AllowOverride
RUN chown -R www-data:www-data /var/www/html && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]