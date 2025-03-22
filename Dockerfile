# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Install dependencies (PDO for PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copy project files into container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port 10000 for the app
EXPOSE 10000

# Ensure Apache runs on port 10000
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:10000/' /etc/apache2/sites-available/000-default.conf

# Start PHP's built-in server (optional for quick testing) or Apache for production
CMD ["apache2-foreground"]
