FROM php:8.2-fpm

# Instalar dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Instalar Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos do projeto
COPY . /var/www/html

WORKDIR /var/www/html

# Rodar composer install para criar pasta vendor
RUN composer install --no-dev --optimize-autoloader

# Cache das configs do Laravel
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 9000

CMD ["php-fpm"]
