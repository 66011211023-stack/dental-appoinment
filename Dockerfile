FROM php:8.3-apache

# ติดตั้ง PHP Extension สำหรับเชื่อมต่อ MySQL
RUN docker-php-ext-install pdo pdo_mysql

# เปิดใช้งาน rewrite module ของ Apache
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
