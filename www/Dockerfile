FROM php:7.4-apache

RUN apt-get update \
    && apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install mysqli

COPY . /var/www/

COPY ./entrypoint.sh /usr/local/bin/custom-entrypoint
ENTRYPOINT ["custom-entrypoint"]

EXPOSE 80