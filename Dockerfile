FROM php:8.3-apache

#ติดตั้ง System dependencies และ PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

#ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

#สั่งสร้าง Autoload mapping โดยไม่พึ่งพา composer.json
RUN composer dump-autoload --optimize --no-interaction || true

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
