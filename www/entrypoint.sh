#!/bin/sh

mkdir -p /var/www/html/uploads

chown -R www-data:www-data /var/www/html/uploads
chmod 755 /var/www/html/uploads

apache2-foreground