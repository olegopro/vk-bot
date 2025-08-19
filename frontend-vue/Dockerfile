FROM node:20-alpine

WORKDIR /app

# Устанавливаем только python3 и создаем символическую ссылку для Python
RUN apk add --update python3 && ln -sf python3 /usr/bin/python

# Копируем файлы проекта
COPY . .

# Устанавливаем зависимости
RUN yarn

EXPOSE 3000

CMD ["yarn", "serve"]
