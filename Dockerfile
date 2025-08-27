FROM php:8.2-apache

# Variáveis
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PATH="$PATH:/var/www/html/vendor/bin"

# Instala extensões essenciais para Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    git \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Instala Node.js (20.x)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite do Apache e ajusta o diretório raiz
RUN a2enmod rewrite && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copia Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia somente os manifests para aproveitar cache
COPY composer.json composer.lock ./
COPY package.json package-lock.json* ./

# Instala dependências PHP e JS
RUN composer install --no-dev --optimize-autoloader --no-scripts && \
    npm install

# Copia o restante do projeto
COPY . .

# Build do frontend com Vite e otimizações
RUN npm run build && \
    composer dump-autoload --optimize && \
    php artisan package:discover

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exposição da porta
EXPOSE 80

# Comando padrão
CMD ["apache2-foreground"]
