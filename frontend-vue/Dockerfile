FROM node:21-alpine

WORKDIR /app

# Устанавливаем зависимости для node-gyp и создаем символическую ссылку для Python
RUN apk add --update python3 make g++ && ln -sf python3 /usr/bin/python

# Копируем файлы зависимостей и устанавливаем зависимости
COPY package*.json ./
RUN yarn

# Копируем остальные файлы проекта
COPY . .

EXPOSE 3000

CMD ["yarn", "serve"]
