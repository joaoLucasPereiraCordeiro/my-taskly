FROM php:8.2-fpm

# Instala dependências do sistema
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

WORKDIR /var/www

# Copiar apenas os arquivos do backend (Dockerfile + .dockerignore cuidam do resto)
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Gerar chave do app
RUN php artisan key:generate

# Cache das configs e rotas
RUN php artisan config:cache && php artisan route:cache

# Porta exposta
EXPOSE 8000

# Comando para rodar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
