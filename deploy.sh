#!/usr/bin/env bash
# --- ЖЁСТКИЙ АВТОДЕПЛОЙ ИЗ GITHUB + МИГРАЦИИ/КЕШИ/ПРАВА ---
set -euo pipefail

APP_DIR="/var/www/laravel"
BRANCH="main"             # меняй, если деплоите не из main

cd "$APP_DIR"

echo "==> [1/8] Maintenance mode ON"
php artisan down || true

echo "==> [2/8] Git sync with origin/$BRANCH"
# Вытягиваем и ПРИВОДИМ КОД К ТОЧНОМУ СОСТОЯНИЮ GITHUB:
git fetch --all --prune
git reset --hard "origin/$BRANCH"
# Удаляем лишние НЕотслеживаемые файлы (игнорируемые НЕ трогаются)
git clean -fd

# ВАЖНО: .env, storage/ и т.п. в обычном Laravel в .gitignore — они НЕ затронутся.
# Проверка: git status --ignored -s  (должны быть в игноре)

echo "==> [3/8] Composer install (prod)"
composer install --no-dev --optimize-autoloader --prefer-dist

echo "==> [4/8] Permissions for runtime dirs"
# Права без sudo: группа www-data, 775; ошибки прав игнорируем
chgrp -R www-data storage bootstrap/cache public/build 2>/dev/null || true
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

echo "==> [5/8] Storage symlink (если нужен)"
php artisan storage:link 2>/dev/null || true

echo "==> [6/8] Migrate DB"
php artisan migrate --force

echo "==> [7/8] Cache refresh"
php artisan cache:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear

# Если фронт менялся, а build не положили — предупредим:
if [ ! -f public/build/manifest.json ]; then
  echo "??  WARN: public/build/manifest.json не найден. Если менялись assets (resources/*), нужно загрузить собранный build."
fi

echo "==> [8/8] Maintenance mode OFF"
php artisan up || true

echo "? Deploy completed successfully."
