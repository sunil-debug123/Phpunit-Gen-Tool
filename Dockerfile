# Use the official Drupal image as a base
FROM drupal:9-apache

# Set working directory
WORKDIR /var/www/html

# Copy the entire web folder from the local directory into the container
COPY ./web .

# Copy composer.json and composer.lock from the local directory into the container
COPY ./composer.json ./composer.lock ./

# Install Composer dependencies
RUN composer install

# Expose port 80
EXPOSE 80
