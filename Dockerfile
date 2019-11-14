FROM php:7.3-alpine3.10

RUN apk add mariadb-client openssh rsync

# COPY Directories:
COPY src /phpsu/src/

# COPY Files:
COPY composer.json phpsu LICENSE /phpsu/

# Install composer and packages:
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer -O - -q | php -- --filename="composer" --install-dir="/bin" && \
    cd /phpsu && \
    composer install --no-dev && \
    composer clear-cache

ENV PATH="/phpsu/:${PATH}"

WORKDIR /app
