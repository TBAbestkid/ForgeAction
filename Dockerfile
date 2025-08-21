FROM php:8.2-apache

# Instala extensões essenciais para Laravel + PostgreSQL e PHP e libs
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl build-essential \
    && docker-php-ext-install pdo pdo_pgsql

# Node.js + npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Habilita mod_rewrite
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

# Composer + npm
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run dev

# Copia o arquivo style.css para o diretório correto
COPY resources/css/style.css /var/www/html/public/css/

# Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080 5173

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]