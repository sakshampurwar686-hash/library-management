# ==============================================================================
# Production-ready Dockerfile for Library Management System (PHP + MySQL + Apache)
# ==============================================================================
FROM php:8.2-apache-bookworm

# Metadata
LABEL maintainer="Library Management System"
LABEL description="Optimized, self-contained container for PHP + MySQL Library Management System"

# Prevent interactive prompts during apt installs
ENV DEBIAN_FRONTEND=noninteractive

# Install required system packages, MariaDB (embedded DB engine), curl (for healthcheck), and socat (optional remote DB proxy)
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    mariadb-client \
    curl \
    socat \
    && rm -rf /var/lib/apt/lists/*

# Install and enable mysqli extension required by includes/db.php
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# Security & performance configurations for Apache
RUN { \
    echo 'ServerTokens Prod'; \
    echo 'ServerSignature Off'; \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory /var/www/html/database>'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
    echo '<FilesMatch "(^#.*#|\.(bak|config|dist|fla|inc|ini|log|psd|sh|sql|sw[op])|~)$">'; \
    echo '    Require all denied'; \
    echo '</FilesMatch>'; \
} > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening

# Production PHP settings
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
    echo 'max_execution_time = 60'; \
    echo 'date.timezone = UTC'; \
    echo 'display_errors = Off'; \
    echo 'display_startup_errors = Off'; \
    echo 'log_errors = On'; \
    echo 'error_log = /dev/stderr'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_strict_mode = 1'; \
    echo 'session.cookie_samesite = "Lax"'; \
} > /usr/local/etc/php/conf.d/library-custom.ini

# Set working directory to Apache document root
WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html/

# Copy and set up the entrypoint script (strip carriage returns in case copied on Windows)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Pre-initialize MariaDB database at build time with library_db schema and sample data
RUN mkdir -p /var/run/mysqld /var/lib/mysql \
    && chown -R mysql:mysql /var/run/mysqld /var/lib/mysql \
    && service mariadb start \
    && mysql -u root -e "CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    && mysql -u root library_db < /var/www/html/database/library_db.sql \
    && mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;" \
    && service mariadb stop

# Set proper ownership and permissions for Apache web server
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose HTTP port
EXPOSE 80

# Health check to ensure the service is running and answering HTTP requests
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fL http://localhost/ || exit 1

# Start services via the entrypoint script
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
