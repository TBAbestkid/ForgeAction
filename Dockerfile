FROM php:8.2-apache

# Instala extensões necessárias
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd

# Habilita rewrite do Apache
RUN a2enmod rewrite

# Copia o app
COPY . /var/www/html

# Permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala dependências PHP
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Inicia Apache na porta dinâmica
CMD ["/bin/sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && \
    sed -i \"s/:80/:${PORT}/\" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground"]
