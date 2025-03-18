FROM php:8.1-apache

COPY . /var/www/html

EXPOSE 80

CMD ["php", "-S", "0.0.0.0:10000"]
