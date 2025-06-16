# Base image com Apache + PHP
FROM php:8.2-apache

# Instala extensões PHP que o Laravel costuma usar
RUN apt-get update && \
    apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip git curl && \
    docker-php-ext-install pdo_mysql mbstring bcmath gd

# Habilita rewrite do Apache
RUN a2enmod rewrite

# Copia todo o seu código para o diretório padrão do Apache
COPY . /var/www/html

# Ajusta permissões de storage e cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala dependências PHP
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Gera APP_KEY caso não exista (útil no primeiro deploy; opcional se você já definir APP_KEY no Railway)
RUN php artisan key:generate

# Substitui a porta 80 pela porta dinâmica do Railway e inicia o Apache em foreground
CMD ["/bin/sh", "-c", "\
    sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && \
    sed -i \"s/:80/:${PORT}/\" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground \
"]
