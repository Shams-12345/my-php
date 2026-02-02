FROM php:8.2-apache

# Apache ته اجازه ورکول
RUN echo "DirectoryIndex index.php index.html" >> /etc/apache2/apache2.conf \
 && echo "<Directory /var/www/html>" >> /etc/apache2/apache2.conf \
 && echo "AllowOverride All" >> /etc/apache2/apache2.conf \
 && echo "Require all granted" >> /etc/apache2/apache2.conf \
 && echo "</Directory>" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html
