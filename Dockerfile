FROM php:8.1-alpine3.15

RUN apk add mariadb-client openssh rsync sshpass bash

# COPY Directories:
COPY src /phpsu/src/

# COPY Files:
COPY composer.json phpsu LICENSE entrypoint.sh /phpsu/

# Install composer and packages:
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer -O - -q | php -- --filename="composer" --install-dir="/bin" && \
    cd /phpsu && \
    composer install --no-dev && \
    composer clear-cache && \
    rm /bin/composer

RUN chmod +x /phpsu/entrypoint.sh && addgroup -g 1000 phpsu && adduser -D -u 1000 -G phpsu phpsu

RUN USER=phpsu && \
    GROUP=phpsu && \
    curl -SsL https://github.com/boxboat/fixuid/releases/download/v0.5/fixuid-0.5-linux-amd64.tar.gz | tar -C /usr/local/bin -xzf - && \
    chown root:root /usr/local/bin/fixuid && \
    chmod 4755 /usr/local/bin/fixuid && \
    mkdir -p /etc/fixuid && \
    printf "user: $USER\ngroup: $GROUP\n" > /etc/fixuid/config.yml

RUN chmod +x /phpsu/phpsu && ln -s /phpsu/phpsu /bin/phpsu

USER phpsu:phpsu

ENTRYPOINT ["/bin/sh", "/phpsu/entrypoint.sh"]

WORKDIR /app
