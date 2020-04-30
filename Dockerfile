FROM php:7.4-alpine3.11

RUN apk add mariadb-client openssh rsync

# COPY Directories:
COPY src /phpsu/src/

# COPY Files:
COPY composer.json phpsu LICENSE /phpsu/

# Install composer and packages:
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer -O - -q | php -- --filename="composer" --install-dir="/bin" && \
    cd /phpsu && \
    composer install --no-dev && \
    composer clear-cache && \
    rm /bin/composer

ENV PATH="/phpsu/:${PATH}"

WORKDIR /app
