FROM debian:buster-slim as runtime

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="6000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="128"

ARG USER_UID=33
ARG USER_GID=33

# common packages
RUN apt-get update\
 && apt-get install -y runit netcat unzip curl gnupg apt-transport-https\
 && apt-get install -y nginx-light\
 && rm -rf /var/lib/apt/lists/*

# php and modules
RUN curl -s https://packages.sury.org/php/apt.gpg | apt-key add -\
 && echo "deb https://packages.sury.org/php/ buster main" > /etc/apt/sources.list.d/php.list\
 && apt-get update\
 && apt-get install -y php7.4-curl php7.4-fpm php7.4-zip php7.4-dom php7.4-mbstring php7.4-intl\
 && apt-get install -y php7.4-mysql php7.4-bcmath php7.4-gd php7.4-apcu php7.4-redis\
 && rm -rf /var/lib/apt/lists/*

# overlay
ADD .docker/alog/overlay /

# working dir and ownership
RUN chown -R www-data:www-data /var/www && /usr/local/bin/changeowner.sh www-data $USER_UID $USER_GID
WORKDIR /var/www/html

# startup
ENTRYPOINT ["/usr/local/bin/run-app.sh"]
CMD ["/usr/bin/runsvdir", "-P", "/etc/service"]

FROM runtime as develop

# env and args
ARG COMPOSER_AUTH
ENV COMPOSER_AUTH=$COMPOSER_AUTH
ARG NPM_TOKEN
ENV NPM_TOKEN=$NPM_TOKEN

# composer
RUN /usr/local/bin/composer-installer.sh\
 && mv composer.phar /usr/local/bin/composer\
 && chmod +x /usr/local/bin/composer\
 && composer --version

# xdebug
RUN apt-get update && apt-get install -o Dpkg::Options::="--force-confold" -y php7.4-xdebug php7.4-pcov\
 && phpdismod -v 7.4 -s cli xdebug\
 && phpdismod -v 7.4 -s fpm pcov\
 && rm -rf /var/lib/apt/lists/*

RUN install -o www-data -g www-data -d /var/www/.cache
RUN install -o www-data -g www-data -d /var/www/.cache/composer

FROM develop as php-builder
COPY --chown=www-data:www-data . /var/www/html
USER www-data
RUN composer install --no-dev --prefer-dist --optimize-autoloader
RUN rm -rf .docker/

FROM runtime as production
COPY --chown=www-data --from=php-builder    /var/www/html        /var/www/html
