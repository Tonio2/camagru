FROM php:7.4-apache

RUN docker-php-ext-install mysqli

COPY . /var/www/html/

COPY ./entrypoint.sh /usr/local/bin/custom-entrypoint
ENTRYPOINT ["custom-entrypoint"]

EXPOSE 80