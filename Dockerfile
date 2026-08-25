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

# ตรวจสอบและสร้าง composer.json หากยังไม่มี พร้อมตั้งค่า PSR-4 Autoload ให้โฟลเดอร์ app/
RUN if [ ! -f composer.json ]; then \
      echo '{"autoload": {"psr-4": {"App\\\\": "app/"}}}' > composer.json; \
    fi

# สั่ง Generate Autoload file
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
