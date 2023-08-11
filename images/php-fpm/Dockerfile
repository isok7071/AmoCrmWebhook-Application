FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget

RUN curl -sL https://getcomposer.org/installer | php -- --install-dir /usr/bin --filename composer

# Рабочая директория для PHP
WORKDIR /var/www/dev

CMD ["php-fpm"]