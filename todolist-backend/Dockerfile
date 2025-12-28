# Base PHP com FPM
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

# Copiar apenas backend
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Criar diretórios de cache com permissão
RUN mkdir -p storage/framework/cache storage/framework/views storage/logs bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

# Gerar chave e cache de configuração (ignora falhas temporárias)
RUN php artisan key:generate || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Expor porta
EXPOSE 8000

# Comando para rodar Laravel no Railway
CMD php artisan migrate --force && php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=8080
