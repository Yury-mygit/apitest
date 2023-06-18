FROM php:8.2-fpm-alpine

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN mkdir -p /var/www/html

WORKDIR /var/www/html
ADD ./back /var/www/html/
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
# COPY composer.* .



RUN docker-php-ext-install pdo pdo_mysql

RUN apk --no-cache add curl

RUN composer  install

RUN delgroup dialout \
    && addgroup -g ${GID} --system laravel \
    && adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf \
    && echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

# RUN echo "Hello world"

USER laravel

# RUN ls

# RUN composer install  
# RUN php artisan key:generate
# RUN php artisan migrate
# RUN php artisan serve

CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]