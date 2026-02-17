FROM php:8.2-apache

# 1. Instala extensões PDO e ferramentas que o Composer exige (zip/unzip)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. Instala o Composer copiando da imagem oficial (Muito mais rápido)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configura o Apache para ler a pasta /public como raiz do site
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Habilita o mod_rewrite (essencial para rotas amigáveis no futuro)
RUN a2enmod rewrite

WORKDIR /var/www/html