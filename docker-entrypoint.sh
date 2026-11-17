#!/usr/bin/env bash
set -e

# Ensure MySQL runtime and data directories exist with proper permissions
mkdir -p /var/run/mysqld /var/lib/mysql
chown -R mysql:mysql /var/run/mysqld /var/lib/mysql
chmod 777 /var/run/mysqld

# Graceful termination handler for SIGTERM and SIGINT
cleanup() {
    echo "[docker-entrypoint] Received termination signal. Shutting down gracefully..."
    if [ -n "$APACHE_PID" ] && kill -0 "$APACHE_PID" 2>/dev/null; then
        echo "[docker-entrypoint] Stopping Apache..."
        kill -WINCH "$APACHE_PID" 2>/dev/null || true
        wait "$APACHE_PID" 2>/dev/null || true
    fi
    if [ -n "$SOCAT_PID" ] && kill -0 "$SOCAT_PID" 2>/dev/null; then
        kill "$SOCAT_PID" 2>/dev/null || true
    fi
    if [ "$USE_EXTERNAL_DB" != "true" ]; then
        echo "[docker-entrypoint] Stopping MariaDB..."
        service mariadb stop || true
    fi
    echo "[docker-entrypoint] Shutdown complete."
    exit 0
}
trap cleanup SIGTERM SIGINT

USE_EXTERNAL_DB=false
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "localhost" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    USE_EXTERNAL_DB=true
    DB_PORT="${DB_PORT:-3306}"
    echo "[docker-entrypoint] External database specified at $DB_HOST:$DB_PORT"
    echo "[docker-entrypoint] Proxying local socket and port 3306 to external database via socat..."

    # Forward TCP port 3306 on 127.0.0.1 to external DB host
    socat TCP-LISTEN:3306,fork,bind=127.0.0.1 TCP:"$DB_HOST":"$DB_PORT" &
    SOCAT_PID=$!

    # Forward Unix socket to external DB host
    rm -f /var/run/mysqld/mysqld.sock
    socat UNIX-LISTEN:/var/run/mysqld/mysqld.sock,fork,mode=777 TCP:"$DB_HOST":"$DB_PORT" &
else
    echo "[docker-entrypoint] Starting local MariaDB service..."
    service mariadb start

    # Wait for MariaDB to become ready
    RETRIES=30
    until mysqladmin ping --silent || [ $RETRIES -eq 0 ]; do
        echo "[docker-entrypoint] Waiting for MariaDB to start ($RETRIES retries left)..."
        sleep 1
        RETRIES=$((RETRIES - 1))
    done

    if [ $RETRIES -eq 0 ]; then
        echo "[docker-entrypoint] Error: MariaDB failed to start within 30 seconds." >&2
        exit 1
    fi

    # Check if database 'library_db' exists
    DB_EXISTS=$(mysql -u root -e "SHOW DATABASES LIKE 'library_db';" | grep library_db || true)
    if [ -z "$DB_EXISTS" ]; then
        echo "[docker-entrypoint] Database 'library_db' not found. Initializing..."
        mysql -u root -e "CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

        if [ -f "/var/www/html/database/library_db.sql" ]; then
            echo "[docker-entrypoint] Importing schema and seed data from database/library_db.sql..."
            mysql -u root library_db < /var/www/html/database/library_db.sql
            echo "[docker-entrypoint] Database schema and initial data imported successfully."
        else
            echo "[docker-entrypoint] Warning: /var/www/html/database/library_db.sql not found."
        fi

        # Ensure root@localhost has passwordless access as configured in db.php
        echo "[docker-entrypoint] Setting root@localhost privileges..."
        mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;" || true
    else
        echo "[docker-entrypoint] Database 'library_db' is already initialized."
    fi
fi

# Ensure web root permissions
chown -R www-data:www-data /var/www/html

echo "[docker-entrypoint] Starting Apache HTTP Server in foreground..."
apache2-foreground &
APACHE_PID=$!

wait "$APACHE_PID"
