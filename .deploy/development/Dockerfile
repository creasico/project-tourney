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

ENV APP_WORKERS_ENABLED=true
ENV AUTORUN_ENABLED=true
ENV AUTORUN_LARAVEL_STORAGE_LINK=false

# Install additional php extensions
# https://github.com/mlocati/docker-php-extension-installer?tab=readme-ov-file
RUN install-php-extensions excimer gd intl imagick sockets xdebug

COPY --chmod=755 .deploy/common/entrypoint.d/ /etc/entrypoint.d/
COPY --chmod=755 .deploy/common/s6-overlay/ /etc/s6-overlay/s6-rc.d/
COPY .deploy/common/nginx/*.conf /etc/nginx/server-opts.d/
COPY .deploy/development/nginx/*.conf /etc/nginx/server-opts.d/
COPY .deploy/development/php/*.ini /usr/local/etc/php/conf.d/

# Change default php.ini location
RUN mv /usr/local/etc/php/conf.d/serversideup-docker-php.ini /usr/local/etc/php/php.ini && \
    chown www-data /etc/s6-overlay/s6-rc.d/user/contents.d

############################################
# Deployment Image
############################################
FROM base AS deploy

ENV SSL_MODE=off
ENV PHP_OPCACHE_ENABLE=0

WORKDIR ${APP_BASE_DIR}

# Install node.js and enable corepack
RUN set -eux && apk --update add --no-cache --purge nodejs-current && corepack enable

USER www-data
