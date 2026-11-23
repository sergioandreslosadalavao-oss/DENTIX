FROM php:8.2-apache

# Instalar librerías del sistema y extensiones de PHP para PostgreSQL y MySQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Carpeta raíz del servidor web
WORKDIR /var/www/html

# Copiar todo el proyecto DENTIX actualizado dentro de la imagen para que funcione en cualquier portátil
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

