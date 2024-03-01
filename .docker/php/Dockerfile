FROM php:8.2-fpm-buster

ARG UID
ARG TIMEZONE

COPY ./oci8/instantclient-basic-linux.x64-21.13.0.0.0dbru.zip /tmp/
COPY ./oci8/instantclient-sdk-linux.x64-21.13.0.0.0dbru.zip /tmp/

COPY php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    libaio1 \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    libxml2-dev \
    acl \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc

# Unzip and install Oracle Instant Client
RUN unzip /tmp/instantclient-basic-linux.x64-21.13.0.0.0dbru.zip -d /usr/local/ \
    && unzip /tmp/instantclient-sdk-linux.x64-21.13.0.0.0dbru.zip -d /usr/local/ \
    && rm /tmp/instantclient*.zip

# Set environment variables for Oracle Instant Client
ENV LD_LIBRARY_PATH /usr/local/instantclient_21_13
ENV ORACLE_BASE /usr/local/instantclient_21_13
ENV TNS_ADMIN /usr/local/instantclient_21_13
ENV ORACLE_HOME /usr/local/instantclient_21_13

# Install OCI8
RUN docker-php-ext-configure oci8 --with-oci8=instantclient,/usr/local/instantclient_21_13 \
    && docker-php-ext-install oci8

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 

RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring

RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN useradd -u ${UID} -m myuser
USER myuser

WORKDIR /var/www/app