#!/bin/bash
set -e

php artisan key:generate

# Rodar migrations (somente se banco estiver pronto)
php artisan migrate --force || true

# Limpa caches (agora em runtime, quando DB existe)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize:clear || true
php artisan key:generate

# Cria link de storage
php artisan storage:link || true

echo "EXTERNAL_API_URL=${EXTERNAL_API_URL:-http://172.17.0.2:9001}" >> .env
echo "EXTERNAL_API_USER=${EXTERNAL_API_USER:-admin}" >> .env
echo "EXTERNAL_API_PASS=${EXTERNAL_API_PASS:-admin}" >> .env

# Força canal de log
if grep -q "^LOG_CHANNEL=" .env; then
  sed -i 's/^LOG_CHANNEL=.*/LOG_CHANNEL=single/' .env
else
  echo "LOG_CHANNEL=single" >> .env
fi

php artisan config:clear
php artisan cache:clear
php artisan optimize:clear

echo "➡️  Canal de log forçado para 'single'"

php artisan tinker --execute="use Illuminate\Support\Facades\Log; Log::info('🚀 Laravel iniciou com LOG_CHANNEL=' . config('logging.default'));"

echo "➡️  Verificação de permissões de storage e bootstrap..."

# Executa comando original (Apache)
exec "$@"
