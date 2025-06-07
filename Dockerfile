FROM php:8.2-cli

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy app files
COPY . /var/www/html

WORKDIR /var/www/html

RUN mkdir -p public/uploads/configs public/uploads/sources \
    && chmod -R 777 public/uploads

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "routes/web.php"]