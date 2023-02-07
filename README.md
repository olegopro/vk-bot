# vk-bot

![ID приложения](./frontend-vue/src/assets/github-images/app-preview.gif)

## Использованные технологии

### Docker
Для запуска приложения используется docker-compose.yml, а так же Dockerfile’s для создания уникальных образов требующихся для запуска приложения(frontend/backend).

### Laravel 8
Используется для RESTful API. Миграции для создания таблиц БД, так же запускается seeder который, записывает базовые настройки в таблицу settings. Для автоматического запуска новых заданий используется “Jobs”. В самом коде используются ресурсные контроллеры, eloquent и query builder.

### Vue 3
Код написан в стиле options api, используется state management - VUEX 4. При написании frontent использовал async/await, promises. Так же использовал vue - route/emits/refs…

## Используемые языки
PHP, SQL, JavaScript, HTML + SCSS

# Установка и запуск
Склонируйте репозиторий  https://github.com/olegopro/vk-bot. Для этого откройте терминал, перейдите в папку где проектами и введите следующую команду для клонирования репозитория на локальный компьютер:
```bash
git clone https://github.com/olegopro/vk-bot
```
После этого перейдите в папку с проектом:
```bash
cd vk-bot/
```
Откройте папку в любом удобном редакторе кода (Sublime/VSCode/PHPStorm). В корне проекта найдите файл `docker-compose.yml`.

В разделе services→php→environment→`VK_API_SERVICE_KEY` вставьте cервисный ключ полученный после создания vk standalone-приложение.

Далее нужно выполнить команду для создания docker контейнера и его запуск:
После этого перейдите в папку с проектом:
```bash
docker-compose up --build
```
Эта команда запустит 5 контейнеров.
**Первый** контейнер - php с laravel 8. **Второй** контейнер - nginx. Он необходим для работы php. Так как мы работаем через php-fpm, а не php-cli. **Третий** контейнер - vue. Если быть точным, то образ node для запуска Vue. **Четвёртый** контейнер - mysql. Пятый контейнер - phpMyAdmin.

После запуска будут доступны следующие адреса:
- [localhost:8181](http://localhost:8181) - phpMyAdmin
- [localhost:8080](http://localhost:8080) - Laravel (api)
- [localhost:3000](http://localhost:3000) - Vue

Обратите внимание, что Vue сразу поддерживает hot reload сразу в контейнере.

Следующим этапом нужно создать таблицы в базе данных с помощью миграции в Laravel. Для этого нужно подключиться к контейнеру где запущен php. Сделать это можно следующей командой:
```bash
docker ps
```
Эта команда выведет запущенные docker контейнеры:
![Список запущенных контейнеров Docker](./frontend-vue/src/assets/github-images/docker-ps.png)

Теперь нужно попасть внутрь контейнера для запуска миграции. Для этого выполним следующую команду:
```bash
docker exec -it 430f0d10a057 bash
```
Далее попадаем в рабочую директорию контейнера `/var/www/html`. Теперь можно выполнить миграцию Laravel с помощью artisan, выполним команду
Теперь нужно попасть внутрь контейнера для запуска миграции. Для этого выполним следующую команду:
```bash
php artisan migrate:fresh
```
Талицы в базе данных `vk-bot` созданы. Далее нужно запустить `SettingSeed` для заполнения таблицы settings стандартными настройками. Так же используем artisan.
```bash
php artisan db:seed --class=SettingSeeder
```
Последний этап, это автоматический запуск новых задач. Для этого в том же контейнере php запустим следующую команду:
```bash
php artisan queue:work
```
Поздравляю! Теперь можно переходить по адресу [localhost:3000](localhost:3000) на frontend и добавлять vk аккаунт через **access token**.

Для получения токена нужно открыть в браузере следующую ссылку: [https://oauth.vk.com/authorize?client_id=**51533209**&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=friends,wall,offline&response_type=token&v=5.131](https://oauth.vk.com/authorize?client_id=51533209&display=page&redirect_uri=https://oauth.vk.com/blank.html&scope=friends,wall,offline&response_type=token&v=5.131), 
где client_id, это id standalone-приложения.

![ID приложения](./frontend-vue/src/assets/github-images/vk-client-id-app.png)

После перехода по ссылке будет предложено ввести имя и пароль от аккаунта, далее будет следующее сообщение, о том, что приложение хочет получить доступ к данным.

![Запрос доступа к данным приложения](./frontend-vue/src/assets/github-images/popup-access-app-require.png)

В адресной строке получаем [https://oauth.vk.com/blank.html#access_token=](https://oauth.vk.com/blank.html#access_token=)**ТОКЕН**

Подробную информацию можно получить в [**документации**](https://dev.vk.com/api/access-token/implicit-flow-user) vkontakte.

P.S. Естественно данный способ добавления аккаунта является технический временным решением. Обычно для авторизации используется OAuth.
