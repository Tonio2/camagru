#!/bin/sh

# Create uploads directory and allow apache to access it
mkdir -p /var/www/uploads
chown -R www-data:www-data /var/www/uploads
chmod 755 /var/www/uploads

# Same for log file
[ ! -f "/var/www/file.log" ] && touch "/var/www/file.log"
chown -R www-data:www-data /var/www/file.log
chmod 755 /var/www/file.log

apache2-foreground