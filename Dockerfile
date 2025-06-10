FROM php:8.2-fpm

RUN docker-php-ext-install mysqli

COPY . /var/www/html

WORKDIR /var/www/html

RUN mkdir -p public/uploads/configs public/uploads/sources \
    && chmod -R 777 public/uploads

EXPOSE 9000

CMD ["php-fpm"]