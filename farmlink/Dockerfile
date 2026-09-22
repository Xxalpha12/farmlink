FROM php:8.2-apache

# Enable the MySQL driver PHP needs (pdo_mysql)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache serves from /var/www/html by default
COPY . /var/www/html/

# uploads/ needs to be writable for product photos
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

# Render gives your container a PORT env var to listen on
ENV PORT=10000
RUN sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
EXPOSE 10000
