FROM php:8.1-fpm

RUN apt-get update && apt-get install -y zip unzip \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && pecl install redis && docker-php-ext-enable redis \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

EXPOSE 9000

COPY . .

RUN composer update
