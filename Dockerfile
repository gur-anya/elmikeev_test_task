FROM php:8.1-cli

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        curl \
        $PHPIZE_DEPS \
        libzip-dev \
        libonig-dev \
        libssl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring bcmath zip

RUN curl -fL -o /usr/local/bin/pie https://github.com/php/pie/releases/latest/download/pie.phar \
    && chmod +x /usr/local/bin/pie \
    && pie install swoole/swoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# faiilfast instead of hanging 24h when the network middlebox blackholes a packet
COPY docker/php/mysqlnd.ini /usr/local/etc/php/conf.d/zz-mysqlnd.ini

WORKDIR /app

EXPOSE 8000

CMD ["sh", "-c", "[ -d vendor ] || composer install --no-interaction --optimize-autoloader; php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000"]

