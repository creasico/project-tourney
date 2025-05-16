# Add user/group
ARG USER_ID=9999
ARG GROUP_ID=9999
ARG PHP_VERSION=8.3

############################################
# Base Image
############################################
FROM serversideup/php:${PHP_VERSION}-fpm-nginx-alpine AS base

USER root

ARG USER_ID
ARG GROUP_ID

ENV APP_WORKERS_ENABLED=false
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=false

# Install additional php extensions
# https://github.com/mlocati/docker-php-extension-installer?tab=readme-ov-file
RUN install-php-extensions excimer gd intl imagick sockets

COPY --chmod=755 .deploy/common/entrypoint.d/ /etc/entrypoint.d/
COPY --chmod=755 .deploy/common/s6-overlay/ /etc/s6-overlay/s6-rc.d/
COPY .deploy/common/nginx/*.conf /etc/nginx/server-opts.d/

# Change default php.ini location
RUN mv /usr/local/etc/php/conf.d/serversideup-docker-php.ini /usr/local/etc/php/php.ini && \
    chown www-data /etc/s6-overlay/s6-rc.d/user/contents.d

RUN mv /init /init-s6
COPY --chmod=755 .deploy/production/init /

############################################
# Deployment Image
############################################
FROM base AS deploy

ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=1

USER www-data
WORKDIR ${APP_BASE_DIR}

# Copy our project files into the container
COPY --chown=www-data:www-data . .

# Make sure our container has the correct permissions
RUN find . -type f -exec chmod 644 {} + && find . -type d -exec chmod 755 {} + && \
    chmod -R 775 bootstrap/cache && chmod +x artisan && \
    rm -rf .deploy

# Install composer dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

VOLUME ["${APP_BASE_DIR}/storage/app"]
