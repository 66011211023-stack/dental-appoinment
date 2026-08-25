FROM php:8.3-apache

# ติดตั้ง System dependencies และ PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

# สั่งติดตั้ง dependencies และเจน Autoload สำหรับ Namespace App\
RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
