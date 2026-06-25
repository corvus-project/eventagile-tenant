FROM node:22-alpine AS assets

WORKDIR /app

# Install frontend dependencies first so Docker can cache this layer
COPY package*.json ./
RUN npm install

# Build Vite assets
COPY . .
RUN npm run build

FROM nginx:alpine

# Copy custom Nginx configuration
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# Copy public assets, including built Vite output
COPY --from=assets /app/public /var/www/html/public
