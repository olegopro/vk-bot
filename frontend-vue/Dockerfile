FROM node:alpine

WORKDIR /app

EXPOSE 3000

COPY package*.json ./

RUN yarn

COPY . .

CMD ["yarn", "serve"]
