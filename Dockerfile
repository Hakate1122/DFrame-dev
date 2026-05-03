FROM php:8.0-fpm-alpine

RUN apk add --no-cache \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    openssl-dev \
    linux-headers 

RUN docker-php-ext-install pdo pdo_mysql mbstring sockets bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

RUN composer run-script post-install-cmd

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]