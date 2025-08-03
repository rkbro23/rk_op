# Lightweight PHP image
FROM php:8.2-alpine

# Install dependencies
RUN apk add --no-cache \
    curl \
    && docker-php-ext-install sockets

# Copy files
WORKDIR /var/www/html
COPY src/ .

# Health check (every 2 mins)
HEALTHCHECK --interval=2m --timeout=3s \
    CMD curl -f http://localhost/proxy/healthcheck || exit 1

# Run PHP server
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080"]
