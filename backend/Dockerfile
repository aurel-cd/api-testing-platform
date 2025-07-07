FROM php:8.3-apache

RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory-limit.ini

RUN apt-get update -y && \
    apt-get install -y \
        libpq-dev \
        libpng-dev \
        unzip \
        libzip-dev \
        supervisor \
        ffmpeg \
        cron \
        redis-tools && \
    docker-php-ext-install pdo pdo_pgsql pgsql gd && \
    pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-install zip
RUN docker-php-ext-install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl

WORKDIR /var/www/html

#Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite macro

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

RUN chmod -R 1777 /tmp

RUN chmod +x startup.sh

CMD ["./startup.sh"]
