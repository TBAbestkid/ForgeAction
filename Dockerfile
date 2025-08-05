FROM php:8.2-apache

# Instala extensões essenciais para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_pgsql

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Define DocumentRoot para /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copia o projeto
COPY . /var/www/html

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Cria entrypoint customizado
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Exponha porta (Railway ignora, mas documenta)
EXPOSE 8080

# Usa o entrypoint customizado e inicia o Apache
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
