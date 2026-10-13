# Build frontend assets in a dedicated Node stage so Laravel's @vite directive
# always finds public/build/manifest.json in the Railway runtime image.
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT}"]
