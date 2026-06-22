FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libzip-dev libssl-dev ca-certificates \
    && docker-php-ext-install pdo pdo_mysql mbstring \
       exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Copy Aiven CA cert from repo into system certs
RUN cp database/certs/aiven-ca.pem /usr/local/share/ca-certificates/aiven-ca.crt \
    && update-ca-certificates

RUN chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

CMD php artisan config:clear && \
    php artisan migrate --force && \
    php artisan db:seed --class=UserSeeder --force && \
    php artisan storage:link && \
    php artisan optimize && \
    apache2-foreground