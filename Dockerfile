FROM php:8.2-apache

# Variável para permitir composer rodar como root
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PATH="$PATH:/var/www/html/vendor/bin"

# Instala extensões essenciais para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git curl libonig-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Instala Node.js (20.x)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite do Apache e ajusta DocumentRoot
RUN a2enmod rewrite \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copia Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia somente os manifests para aproveitar cache
COPY composer.json composer.lock package.json package-lock.json* vite.config.* ./

# Instala dependências PHP e JS
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && npm install

# Copia o restante do projeto
COPY . .

# Build frontend com Vite (vai gerar public/build)
RUN npm run build \
    && php artisan package:discover \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposição da porta
EXPOSE 8080

# Inicia Apache
CMD ["apache2-foreground"]
