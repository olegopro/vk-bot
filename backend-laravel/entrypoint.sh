#!/bin/bash
# Экспорт переменных окружения в файл /etc/environment для их доступности во всех процессах
printenv >> /etc/environment

# Добавление задач cron из файла и их запуск
crontab /var/www/html/laravel-cron
cron

# Запуск php-fpm как основного процесса контейнера
exec docker-php-entrypoint php-fpm
