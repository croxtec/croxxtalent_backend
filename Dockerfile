FROM php:7.4-fpm

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install dependencies for LaravelSnappy(SnappyPDF and SnappyImage)
RUN apt-get install -y \
    libxrender1 \
    libfontconfig1 \
    libx11-dev \
    libjpeg62 \
    libxtst6
    # wget \
    # && wget https://github.com/h4cc/wkhtmltopdf-amd64/blob/master/bin/wkhtmltopdf-amd64?raw=true -O /usr/local/bin/wkhtmltopdf-amd64 \
    # && chmod +x /usr/local/bin/wkhtmltopdf-amd64 \
    # && wget https://github.com/h4cc/wkhtmltoimage-amd64/blob/master/bin/wkhtmltoimage-amd64?raw=true -O /usr/local/bin/wkhtmltoimage-amd64 \
    # && chmod +x /usr/local/bin/wkhtmltoimage-amd64

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www/croxxtalent-backend

USER $user