FROM php:8.2-fpm-alpine

ARG UID
ARG GID

ENV UID=${UID}
ENV GID=${GID}

RUN mkdir -p /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apk add git

RUN git clone https://github.com/Yury-mygit/apitest.git




# ADD /back /var/www/html/

# CMD ['CMD', 'co']

RUN docker-php-ext-install pdo pdo_mysql

RUN apk --no-cache add curl

RUN mkdir -p /usr/src/php/ext/redis \
    && curl -L https://github.com/phpredis/phpredis/archive/5.3.4.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
    && echo 'redis' >> /usr/src/php-available-exts \
    && docker-php-ext-install redis

RUN delgroup dialout \
    && addgroup -g ${GID} --system laravel \
    && adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

RUN sed -i "s/user = www-data/user = laravel/g" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "s/group = www-data/group = laravel/g" /usr/local/etc/php-fpm.d/www.conf \
    && echo "php_admin_flag[log_errors] = on" >> /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html/apitest/back

RUN composer install

# RUN php artisan key:generate
# RUN php artisan migrate
# RUN php artisan serve

WORKDIR /var/www/html

COPY .env /var/www/html/apitest/back/

USER laravel

# RUN composer install

# CMD ["php","artisan","serve","--host=0.0.0.0"]
# CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]