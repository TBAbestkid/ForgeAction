FROM php:8.2-fpm

# Instalar extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Copiar sua app
COPY . /var/www/html

# Setar o working dir
WORKDIR /var/www/html

# Rodar comandos do Laravel
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Expõe a porta do PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
