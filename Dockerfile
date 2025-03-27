# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем расширения
RUN docker-php-ext-install pdo pdo_mysql

# RUN docker-php-ext-install gd

# RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && \
#     docker-php-ext-configure gd --with-freetype --with-jpeg && \
#     docker-php-ext-install gd


# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы проекта
WORKDIR /var/www/html
COPY . .

# Устанавливаем права на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Запускаем Apache
CMD ["apache2-foreground"]

