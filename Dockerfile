FROM php:8.2-apache

# 1) Instala extensões PHP que o Laravel necessita
RUN apt-get update && \
    apt-get install -y libpng-dev libonig-dev libxml2-dev zip unzip git curl && \
    docker-php-ext-install pdo_mysql mbstring bcmath gd

# 2) Habilita mod_rewrite
RUN a2enmod rewrite

# 3) Ajusta o DocumentRoot para apontar para public/
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# 4) Copia o projeto inteiro
COPY . /var/www/html

# 5) Ajusta permissões (inclui public, storage e cache)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 6) Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 7) Instala dependências PHP
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader

# 8) Exponha porta (opcional, Railway ignora, mas documenta)
EXPOSE 8080

# 9) Substitui a porta e inicia o Apache em foreground
CMD ["/bin/sh", "-c", "\
    sed -i \"s/Listen 80/Listen ${PORT}/\" /etc/apache2/ports.conf && \
    sed -i \"s/:80/:${PORT}/\" /etc/apache2/sites-enabled/000-default.conf && \
    apache2-foreground \
"]
