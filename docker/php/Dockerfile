FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        chromium \
        fonts-liberation \
        fonts-noto-core \
        git \
        unzip \
        libicu-dev \
        libpng-dev \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install bcmath intl pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN groupadd --gid 1000 propix \
    && useradd --uid 1000 --gid propix --shell /bin/bash --create-home propix \
    && chown -R propix:propix /var/www/html

USER propix
