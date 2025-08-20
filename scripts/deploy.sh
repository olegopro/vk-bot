#!/bin/bash

# Скрипт развертывания VK-BOT приложения
# Принимает переменные окружения: SERVER_HOST, VK_API_SERVICE_KEY

set -e  # Остановка при ошибке

echo "🚀 Начинаем развертывание VK-BOT..."

# Удаление старой директории проекта (если существует)
echo "📁 Очистка старых файлов..."
sudo rm -rf /var/www/vk-bot

# Клонирование репозитория
echo "📥 Клонирование репозитория..."
cd /var/www
git clone https://github.com/olegopro/vk-bot.git
cd vk-bot

# Установка зависимостей PHP
echo "🐘 Установка зависимостей PHP..."
cd backend-laravel
composer install --no-dev --optimize-autoloader

# Создание .env файла для продакшена
echo "⚙️ Настройка конфигурации backend..."
cat > .env << EOF
APP_NAME=VKBot
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=http://${SERVER_HOST}

LOG_CHANNEL=null
LOG_LEVEL=emergency

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vk-bot
DB_USERNAME=root
DB_PASSWORD=password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

ACCESS_TOKEN_SALT=$(openssl rand -hex 16)
VK_API_SERVICE_KEY=${VK_API_SERVICE_KEY}
EOF

# Генерация ключа приложения
echo "🔑 Генерация ключей приложения..."
php artisan key:generate --force
php artisan vendor:publish --tag=log-viewer-assets --force

# Выполнение миграций
echo "🗄️ Выполнение миграций базы данных..."
php artisan migrate:fresh --force

# Очистка и кеширование
echo "🧹 Очистка и кеширование..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Установка зависимостей frontend и сборка
echo "🎨 Сборка frontend..."
cd ../frontend-vue
yarn install

# Создание .env файла для frontend
cat > .env << EOF
VITE_API_URL=http://${SERVER_HOST}:8080/api
EOF

yarn build

# Перезапуск сервисов
echo "🔄 Перезапуск сервисов..."
sudo systemctl reload php8.2-fpm || true
sudo systemctl reload nginx || sudo systemctl reload apache2 || true

# Запуск Laravel сервера
echo "🚀 Запуск Laravel сервера..."
cd /var/www/vk-bot/backend-laravel
nohup php artisan serve --host=0.0.0.0 --port=8080 > /dev/null 2>&1 &
nohup php artisan queue:work > /dev/null 2>&1 &

echo "✅ Развертывание завершено успешно!"
echo "🌐 Приложение доступно по адресу: http://${SERVER_HOST}:8080"