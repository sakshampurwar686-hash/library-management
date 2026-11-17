
#!/usr/bin/env bash
set -e

# Prepare MariaDB directories
mkdir -p /var/run/mysqld /var/lib/mysql
chown -R mysql:mysql /var/run/mysqld /var/lib/mysql
chmod 777 /var/run/mysqld

# Graceful shutdown
cleanup() {
    echo "[docker-entrypoint] Shutting down..."

    if [ -n "${APACHE_PID:-}" ] && kill -0 "$APACHE_PID" 2>/dev/null; then
        kill -WINCH "$APACHE_PID" 2>/dev/null || true
        wait "$APACHE_PID" 2>/dev/null || true
    fi

    if [ -n "${SOCAT_PID:-}" ] && kill -0 "$SOCAT_PID" 2>/dev/null; then
        kill "$SOCAT_PID" 2>/dev/null || true
    fi

    if [ "${USE_EXTERNAL_DB:-false}" != "true" ]; then
        service mariadb stop || true
    fi

    exit 0
}

trap cleanup SIGTERM SIGINT

USE_EXTERNAL_DB=false

# External database configuration
if [ -n "${DB_HOST:-}" ] && \
   [ "$DB_HOST" != "localhost" ] && \
   [ "$DB_HOST" != "127.0.0.1" ]; then

    USE_EXTERNAL_DB=true
    DB_PORT="${DB_PORT:-3306}"

    echo "[docker-entrypoint] Using external database: $DB_HOST:$DB_PORT"

    socat TCP-LISTEN:3306,fork,bind=127.0.0.1 \
        TCP:"$DB_HOST":"$DB_PORT" &
    SOCAT_PID=$!

else

    # Start local MariaDB
    echo "[docker-entrypoint] Starting local MariaDB..."
    service mariadb start

    # Wait for MariaDB
    RETRIES=30

    until mysqladmin ping --silent || [ "$RETRIES" -eq 0 ]; do
        echo "[docker-entrypoint] Waiting for MariaDB..."
        sleep 1
        RETRIES=$((RETRIES - 1))
    done

    if [ "$RETRIES" -eq 0 ]; then
        echo "[docker-entrypoint] ERROR: MariaDB failed to start."
        exit 1
    fi

    # Initialize database if missing
    DB_EXISTS=$(mysql -u root -N -e \
        "SHOW DATABASES LIKE 'library_db';" || true)

    if [ -z "$DB_EXISTS" ]; then

        echo "[docker-entrypoint] Creating library_db..."

        mysql -u root -e \
            "CREATE DATABASE library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

        if [ -f "/var/www/html/database/library_db.sql" ]; then
            echo "[docker-entrypoint] Importing database..."
            mysql -u root library_db < /var/www/html/database/library_db.sql
        else
            echo "[docker-entrypoint] WARNING: SQL file not found."
        fi

    else
        echo "[docker-entrypoint] library_db already exists."
    fi

    # Create application database user
    if [ -z "${DB_PASSWORD:-}" ]; then
        echo "[docker-entrypoint] ERROR: DB_PASSWORD is not configured."
        exit 1
    fi

    echo "[docker-entrypoint] Configuring library_user..."

    # Restrict password to simple alphanumeric characters
    if [[ ! "$DB_PASSWORD" =~ ^[A-Za-z0-9_-]{12,64}$ ]]; then
        echo "[docker-entrypoint] ERROR: DB_PASSWORD must be 12-64 characters using letters, numbers, _ or -."
        exit 1
    fi

    mysql -u root <<SQL
CREATE USER IF NOT EXISTS 'library_user'@'%'
IDENTIFIED BY '${DB_PASSWORD}';

ALTER USER 'library_user'@'%'
IDENTIFIED BY '${DB_PASSWORD}';

GRANT ALL PRIVILEGES ON library_db.*
TO 'library_user'@'%';

FLUSH PRIVILEGES;
SQL

    echo "[docker-entrypoint] Database user configured successfully."

fi

# Apache permissions
chown -R www-data:www-data /var/www/html

# Start Apache
echo "[docker-entrypoint] Starting Apache..."

apache2-foreground &
APACHE_PID=$!

wait "$APACHE_PID"
