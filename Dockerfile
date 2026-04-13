FROM php:8.2-apache

# 1. Extensiones
RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

# 2. Habilitar mod_rewrite
RUN a2enmod rewrite

# 3. Permitir .htaccess (necesario para tus rutas)
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 4. Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# 5. Copiar solo composer.json primero (optimiza caché de Docker)
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 6. Copiar el resto del proyecto
COPY . /var/www/html/

EXPOSE 80