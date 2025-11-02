# Imagem base com PHP, Composer e extensões necessárias
FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    libzip-dev && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar o código do projeto
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Gerar cache do framework
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Expor a porta do servidor
EXPOSE 8000

# Comando para rodar o servidor Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
