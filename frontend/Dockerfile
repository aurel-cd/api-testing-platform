# Stage 1: Develompent stage
FROM node:20

WORKDIR /var/www/html

COPY . .

RUN npm install

EXPOSE 5173

CMD ["npm", "run", "dev"]