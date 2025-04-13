FROM ghcr.io/sylius/sylius-php:8.3-fixuid-xdebug-alpine

USER root

# install php-soap extension
RUN set -ex && apk --no-cache add libxml2-dev
RUN docker-php-ext-install soap

USER sylius:sylius
