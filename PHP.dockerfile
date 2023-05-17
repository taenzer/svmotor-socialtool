## WICHTIG: Wenn Änderungen an dieser Datei gemacht werden, muss danach docker-compose build aufgerufen
## werden, um das Image neu zu bauen!

FROM php:fpm

### Installing PHP Extensions
# RUN <Linux Befehl der im PHP Container ausgeführt werden soll>
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli

# Für besseres debugging
RUN pecl install -f xdebug && docker-php-ext-enable xdebug 

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install unzip utility and libs needed by zip PHP extension 
RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libzip-dev \
    unzip
RUN docker-php-ext-install zip

# Installing GD Extension for Image Processing
RUN apt-get install -y libpng-dev
RUN docker-php-ext-install gd