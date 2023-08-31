#!/bin/sh

mkdir -p /var/www/uploads

chown -R www-data:www-data /var/www/uploads
chmod 755 /var/www/uploads

apache2-foreground