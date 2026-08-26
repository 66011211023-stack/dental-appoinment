FROM php:8.3-apache
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

# เปลี่ยน DocumentRoot ของ Apache เป็นโฟลเดอร์ public (หากโครงสร้างเว็บเก็บ index.php ไว้ที่ public)
# หาก index.php อยู่ที่ root ของโปรเจกต์ สามารถตัด 2 บรรทัด SED ด้านล่างนี้ออกได้เลย
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/htdocs!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80