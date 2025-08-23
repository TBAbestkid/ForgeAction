#!/bin/bash
set -e

# Rodar comandos Laravel
php artisan storage:link || true
php artisan migrate --force

# Limpa caches (evita erros em Render)
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear

# Inicia Apache
exec "$@"
