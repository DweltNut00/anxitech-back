FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql gd mbstring zip

RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html

RUN composer install --optimize-autoloader --no-scripts --no-interaction

COPY . /var/www/html/

EXPOSE 80

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80} -t /var/www/html"]