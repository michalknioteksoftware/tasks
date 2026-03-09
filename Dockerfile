FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    unzip \
    libpq-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    $PHPIZE_DEPS \
    rabbitmq-c-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    xml \
    mbstring

RUN pecl install amqp && docker-php-ext-enable amqp \
    && pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /var/www

USER app

# Allow git in mounted repo (host ownership differs from container user)
RUN git config --global --add safe.directory /var/www

EXPOSE 9000

CMD ["php-fpm"]
