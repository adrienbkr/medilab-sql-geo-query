FROM php:7.2.2-apache
COPY ./php /var/www/html/
# WORKDIR /var/www/html/
# RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update
RUN apt-get install -y libpq-dev
RUN docker-php-ext-install pdo pdo_pgsql
