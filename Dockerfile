FROM php:8.2-alpine

# Create directory first
WORKDIR /var/www/html
RUN mkdir -p /var/www/html/proxy

# Copy only needed files
COPY proxy/ /var/www/html/proxy/

# Health check (uses lightweight wget instead of curl)
HEALTHCHECK --interval=2m --timeout=3s \
    CMD wget -q --spider http://localhost/proxy/healthcheck || exit 1

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]
