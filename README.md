# Simple Transaction Webapp

Laravel application with a Blade/Vite frontend in `backend-laravel`.

## Docker Compose

Start the local container stack from the repository root:

```bash
docker compose up --build
```

The compose stack includes:

- `mysql` on `localhost:${MYSQL_PORT:-3306}`
- `phpmyadmin` on `http://localhost:${PHPMYADMIN_PORT:-8080}`
- `backend` on `http://localhost:${BACKEND_PORT:-8000}`
- `vite` on `http://localhost:${VITE_PORT:-5173}`

Default database credentials are:

- Database: `simple_transaction`
- Username: `root`
- Password: `secret`

Create a local `.env` from `.env.example` to customize Docker Compose ports, database name, the MySQL root password, or migration behavior.

```bash
cp .env.example .env
docker compose up --build
```

The `vite` service runs Laravel Vite in dev mode for Blade styles and scripts. For production-style asset verification, build frontend assets from the Laravel app directory:

```bash
cd backend-laravel
npm install
npm run build
```

The backend image installs Xdebug and loads `docker/backend/xdebug.ini`. Migrations run automatically by default through `SIMPLE_TRANSACTION_RUN_MIGRATIONS=true` in `docker-compose.yml`.
