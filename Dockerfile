FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

RUN a2enmod rewrite

COPY . /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --optimize-autoloader --no-scripts --no-interaction

EXPOSE 80