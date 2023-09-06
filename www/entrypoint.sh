#!/bin/sh

# Create uploads directory and allow apache to access it
mkdir -p /var/www/uploads
chown -R www-data:www-data /var/www/uploads
chmod 755 /var/www/uploads

# Same for log file
[ ! -f "/var/www/file.log" ] && touch "/var/www/file.log"
chown -R www-data:www-data /var/www/file.log
chmod 755 /var/www/file.log

# root is the person who gets all mail for userids < 1000
echo "root=labalette.antoine@gmail.com" >> /etc/ssmtp/ssmtp.conf

# Here is the gmail configuration (or change it to your private smtp server)
echo "mailhub=smtp.gmail.com:465" >> /etc/ssmtp/ssmtp.conf
echo "FromLineOverride=YES" >> /etc/ssmtp/ssmtp.conf
echo "AuthUser=labalette.antoine@gmail.com" >> /etc/ssmtp/ssmtp.conf
echo "AuthPass=$SMTP_PASSWORD" >> /etc/ssmtp/ssmtp.conf

echo "UseTLS=YES" >> /etc/ssmtp/ssmtp.conf

# Set up php sendmail config
echo "sendmail_path=sendmail -i -t" >> /usr/local/etc/php/conf.d/php-sendmail.ini

apache2-foreground