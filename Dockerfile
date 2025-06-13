FROM php:8.2-fpm

# Instalar dependências
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Copiar os arquivos da aplicação
COPY . /var/www/html
WORKDIR /var/www/html

# Instalar as dependências do composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Copiar config do Nginx
COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 8080

CMD service nginx start && php-fpm
