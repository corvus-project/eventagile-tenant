FROM nginx:alpine

# Copy custom Nginx configuration
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy public assets
COPY ./public /var/www/html/public
