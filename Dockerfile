
# PHP + Apache + MariaDB
FROM php:8.2-apache-bookworm

LABEL maintainer="Library Management System"

ENV DEBIAN_FRONTEND=noninteractive

# Install MariaDB and required utilities
RUN apt-get update && apt-get install -y --no-install-recommends \
    mariadb-server \
    mariadb-client \
    curl \
    socat \
    && rm -rf /var/lib/apt/lists/*

# Install mysqli
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# Enable Apache modules
RUN a2enmod rewrite headers

# Apache security configuration
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

# PHP configuration
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

WORKDIR /var/www/html

# Copy project
COPY . /var/www/html/

# Install entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Initialize MariaDB and import database schema
RUN mkdir -p /var/run/mysqld /var/lib/mysql \
    && chown -R mysql:mysql /var/run/mysqld /var/lib/mysql \
    && service mariadb start \
    && mysql -u root -e "CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" \
    && if [ -f /var/www/html/database/library_db.sql ]; then mysql -u root library_db < /var/www/html/database/library_db.sql; fi \
    && service mariadb stop

# Application permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -fL http://localhost/ || exit 1

ENTRYPOINT ["/bin/bash", "/usr/local/bin/docker-entrypoint.sh"]
