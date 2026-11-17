FROM php:8.3-cli
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY . /app
EXPOSE 3000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-3000} -t public"]
