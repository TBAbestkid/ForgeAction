FROM php:8.2-apache

# Instala extensões essenciais para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Instala Node.js (20.x)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Define DocumentRoot para /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copia Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia package.json e composer.json antes (para cache de build)
COPY composer.json composer.lock package.json package-lock.json* vite.config.* ./

# Instala dependências PHP e JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install

# Copia o restante do projeto
COPY . .

# Faz o build do Vite (arquivos vão para /public/build)
RUN npm run build

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposição de portas
EXPOSE 8080

CMD ["apache2-foreground"]
