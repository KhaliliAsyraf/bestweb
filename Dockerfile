FROM php:8.3-fpm

# Install required system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    curl \
    ca-certificates \
    gnupg2 \
    apt-transport-https \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip xml \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Allow git to trust project directory (avoids ownership warning)
RUN git config --global --add safe.directory /var/www/html

COPY . .

# COPY composer.json composer.lock ./
RUN rm -rf vendor composer.lock
RUN composer install --no-dev --prefer-dist --no-autoloader --no-progress --no-interaction --no-scripts --ignore-platform-reqs --optimize-autoloader || true

# Install Nginx
RUN apt-get update && apt-get install -y nginx \
    && rm -f /etc/nginx/conf.d/default.conf

# Copy Nginx config
ARG ENVIRONMENT=local
COPY nginx/default.${ENVIRONMENT}.conf /etc/nginx/conf.d/default.conf

# # Clean old Laravel cache (fixes ghost package discovery issues)
# RUN rm -rf bootstrap/cache/*.php config/l5-swagger.php || true \
#     && composer install --prefer-dist --no-interaction --optimize-autoloader

RUN mkdir -p bootstrap/cache config && \
    find bootstrap/cache -type f -name '*.php' -delete 2>/dev/null || true && \
    rm -f config/l5-swagger.php 2>/dev/null || true && \
    composer install --prefer-dist --no-interaction --no-scripts --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

COPY wait-for-db.sh /usr/local/bin/wait-for-db.sh
RUN chmod +x /usr/local/bin/wait-for-db.sh

EXPOSE 80
CMD ["entrypoint"]
