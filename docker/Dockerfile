ARG PHP_VERSION=8.2.8
ARG FPM_ALPINE_VERSION=3.18
ARG COMPOSER_VERSION=2.3.5

FROM composer:${COMPOSER_VERSION} AS composer_stage
FROM --platform=linux/amd64 php:${PHP_VERSION}-fpm-alpine${FPM_ALPINE_VERSION} AS php_stage

ADD ./docker/php/php.ini /usr/local/etc/php/php.ini
ADD ./docker/php/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

ENV WORK_DIR /var/www/integration

ENV PHPIZE_DEPS \
    linux-headers \
    autoconf \
    gcc \
    libc-dev \
    make \
    curl-dev \
    musl-dev \
    zlib-dev \
    cyrus-sasl-dev \
    libmemcached-dev \
    libzip-dev \
    g++ \
    re2c \
    perl \
    perl-error     \
    perl-git \
    m4 \
    icu-dev

ENV PERMANENT_DEPS \
    openssh \
    libgcc \
    libstdc++ \
    libbz2 \
    libmemcached-dev \
    file \
    binutils \
    gmp \
    libgomp \
    libatomic \
    zlib \
    libzip \
    icu-libs \
    git

RUN set -xe \
    && apk update \
    && apk add --no-cache ${PERMANENT_DEPS} \
    && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS}

RUN docker-php-ext-install -j$(nproc) sockets \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-install -j$(nproc) pcntl \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) pdo \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && pecl install memcached \
    && docker-php-ext-enable memcached \
    && pecl install xhprof \
    && docker-php-ext-enable xhprof \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/*

FROM php_stage AS vendor_stage

COPY . ${WORK_DIR}
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer
### https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR ${WORK_DIR}

RUN composer install -o --no-interaction

# build app
FROM php_stage

WORKDIR ${WORK_DIR}

COPY . .

COPY --from=vendor_stage --chown=www-data:www-data ${WORK_DIR}/vendor ${WORK_DIR}/vendor

RUN chown -R www-data:www-data ${WORK_DIR}

RUN chmod 777 -R ${WORK_DIR}/config/data/cache

# Создаем директорию, если ее нет
RUN mkdir -p /var/www/integration/src/CrmApi/src/templates

# Устанавливаем права доступа
RUN chmod 777 -R /var/www/integration/src/CrmApi/src/templates

# Права на папку кэш
RUN chmod 777 -R ${WORK_DIR}/config/autoload/../data/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
