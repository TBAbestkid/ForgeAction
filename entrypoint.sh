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

echo "EXTERNAL_API_URL=${EXTERNAL_API_URL:-http://172.30.0.2:9001}" >> .env
echo "EXTERNAL_API_USER=${EXTERNAL_API_USER:-admin}" >> .env
echo "EXTERNAL_API_PASS=${EXTERNAL_API_PASS:-admin}" >> .env

if grep -q "^LOG_CHANNEL=" .env; then
  sed -i 's/^LOG_CHANNEL=.*/LOG_CHANNEL=stack/' .env
else
  echo "LOG_CHANNEL=stack" >> .env
fi

# remove LOG_STACK se existir (pra evitar confusão)
sed -i '/^LOG_STACK=/d' .env

# limpa caches do Laravel
php artisan config:clear || true
php artisan cache:clear || true
php artisan optimize:clear || true

echo "➡️  Canal de log forçado para 'stack' (saída em stderr)"

# registra log de teste direto na saída
php artisan tinker --execute="use Illuminate\Support\Facades\Log; Log::info('🚀 Laravel iniciou com LOG_CHANNEL=' . config('logging.default'));"

echo "➡️  Verificação de permissões de storage e bootstrap..."

echo "➡️  Corrigindo permissões do Laravel..."

# Garante que as pastas existem
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Cria o arquivo de log se não existir
touch /var/www/html/storage/logs/laravel.log

# Corrige permissões (ESSENCIAL)
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "✅ Permissões ajustadas"

# Executa comando original (Apache)
exec "$@"
