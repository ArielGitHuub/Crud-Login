FROM php:8.3-apache

# pdo_sqlite ya viene incluido en la imagen oficial de PHP
WORKDIR /var/www/html
COPY . .

# Apache (www-data) necesita escribir data.sqlite y su carpeta
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
