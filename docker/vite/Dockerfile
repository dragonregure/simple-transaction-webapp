FROM node:24-bookworm-slim

WORKDIR /app

COPY backend-laravel/package.json backend-laravel/package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY backend-laravel ./
COPY docker/vite/entrypoint.sh /usr/local/bin/simple-transaction-vite-entrypoint

RUN chmod +x /usr/local/bin/simple-transaction-vite-entrypoint

EXPOSE 5173

ENTRYPOINT ["simple-transaction-vite-entrypoint"]
