FROM php:8.3-cli

RUN apt-get update \
  && apt-get install -y --no-install-recommends libsqlite3-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pcntl pdo_sqlite

WORKDIR /app
