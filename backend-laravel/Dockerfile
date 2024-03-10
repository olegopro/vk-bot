FROM php:8.3-fpm

RUN apt-get update && apt-get install -y zip unzip cron vim \
    && docker-php-ext-install pdo pdo_mysql pcntl \
    && pecl install redis && docker-php-ext-enable redis \
    && pecl install xdebug && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/docker-php-ext-custom.ini
RUN apt-get install -y bash

WORKDIR /var/www/html

EXPOSE 9000

COPY . .

RUN composer update

# Добавление конфигурации Xdebug
#RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.client_port=9003' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#    && echo "xdebug.idekey = PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Замена CMD на новый entrypoint скрипт
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
