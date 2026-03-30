FROM dunglas/frankenphp:latest-php8.3

RUN install-php-extensions \
    intl \
    opcache \
    pdo_mysql \
    zip \
    apcu

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV APP_ENV=dev
ENV APP_DEBUG=1
ENV SERVER_NAME=":80"
