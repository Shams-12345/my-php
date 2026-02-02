FROM php:8.1-apache
COPY index.php /var/www/html/index.php
RUN docker-php-ext-install pdo pdo_mysql || true
EXPOSE 80
CMD ["apache2-foreground"]