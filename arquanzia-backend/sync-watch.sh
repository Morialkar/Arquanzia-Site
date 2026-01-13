#!/bin/bash

# Sync watcher for Arquanzia backend
# Usage: ./sync-watch.sh

REMOTE_HOST="arquanzia"
REMOTE_DIR="arquanzia.com"
LOCAL_DIR="$(dirname "$0")"
PHP_BIN="/opt/alt/php84/usr/bin/php"

# Excludes - files/dirs to NOT sync
EXCLUDES=(
    ".git"
    ".env"
    "node_modules"
    "vendor"
    "storage"
    "bootstrap/cache"
    ".DS_Store"
    "sync-watch.sh"
)

# Build rsync exclude args
EXCLUDE_ARGS=""
for exclude in "${EXCLUDES[@]}"; do
    EXCLUDE_ARGS="$EXCLUDE_ARGS --exclude=$exclude"
done

# Track composer.lock hash
LAST_COMPOSER_HASH=""

run_remote_composer() {
    echo "📦 Running composer install on server..."
    ssh "$REMOTE_HOST" "cd $REMOTE_DIR && $PHP_BIN \$(which composer) install --no-dev --optimize-autoloader"
    echo "✅ Composer done"
}

run_remote_migrations() {
    echo "🗄️ Running migrations on server..."
    ssh "$REMOTE_HOST" "cd $REMOTE_DIR && $PHP_BIN artisan migrate --force"
    echo "✅ Migrations done"
}

fix_permissions() {
    echo "🔐 Fixing permissions on server..."
    ssh "$REMOTE_HOST" "cd $REMOTE_DIR && chmod -R 775 storage bootstrap/cache"
    echo "✅ Permissions fixed"
}

do_sync() {
    rsync -avz --delete $EXCLUDE_ARGS "$LOCAL_DIR/" "$REMOTE_HOST:$REMOTE_DIR/"
    
    # Check if composer.lock changed
    CURRENT_HASH=$(md5 -q "$LOCAL_DIR/composer.lock" 2>/dev/null || echo "")
    if [ -n "$CURRENT_HASH" ] && [ "$CURRENT_HASH" != "$LAST_COMPOSER_HASH" ]; then
        LAST_COMPOSER_HASH="$CURRENT_HASH"
        run_remote_composer
    fi
    
    # Check if migrations trigger file exists
    if [ -f "$LOCAL_DIR/.run-migrations" ]; then
        rm "$LOCAL_DIR/.run-migrations"
        run_remote_migrations
    fi
}

# Initial sync
echo "🚀 Initial sync to $REMOTE_HOST:$REMOTE_DIR..."
rsync -avz --delete $EXCLUDE_ARGS "$LOCAL_DIR/" "$REMOTE_HOST:$REMOTE_DIR/"
LAST_COMPOSER_HASH=$(md5 -q "$LOCAL_DIR/composer.lock" 2>/dev/null || echo "")
run_remote_composer
fix_permissions
echo "✅ Initial sync complete"
echo "💡 To run migrations: touch .run-migrations"

# Watch for changes - only important directories
echo "👀 Watching for changes... (Ctrl+C to stop)"

WATCH_DIRS=(
    "$LOCAL_DIR/app"
    "$LOCAL_DIR/config"
    "$LOCAL_DIR/routes"
    "$LOCAL_DIR/resources"
    "$LOCAL_DIR/public"
    "$LOCAL_DIR/database"
    "$LOCAL_DIR/composer.json"
    "$LOCAL_DIR/composer.lock"
    "$LOCAL_DIR/.run-migrations"
)

fswatch -o "${WATCH_DIRS[@]}" | while read; do
    echo "🔄 Change detected, syncing..."
    do_sync
    echo "✅ Synced at $(date '+%H:%M:%S')"
done
