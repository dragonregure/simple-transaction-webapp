# Simple Transaction Webapp

A Laravel application for recording simple financial transactions against a chart of accounts. The application lives in `backend-laravel` and uses Blade with Vite for frontend assets.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL 8.4
- Blade, Vite, and Tailwind CSS
- Docker Compose for local development

## Running With Docker

From the repository root:

```bash
cp .env.example .env
docker compose up --build
```

Available services:

- App: `http://localhost:8000`
- Vite: `http://localhost:5173`
- phpMyAdmin: `http://localhost:8080`
- MySQL: `localhost:3306`

Default database credentials:

```text
Database: simple_transaction
Username: root
Password: secret
```

The backend container runs migrations automatically when `SIMPLE_TRANSACTION_RUN_MIGRATIONS=true`.

The backend image installs Xdebug and loads `docker/backend/xdebug.ini`, so containerized debugging is available during development.

## Local Development

For local development without Docker, configure MySQL first, then run:

```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
composer dev
```

The Laravel `.env.example` defaults to the same MySQL database name and credentials used by Docker.

## Validation

Run checks from `backend-laravel`:

```bash
composer test
composer analyse
composer lint
npm run build
```

## Continuous Integration

GitHub Actions runs the Simple Transaction CI workflow after pushes and pull requests to `main` and `develop`. The workflow installs Laravel dependencies, prepares a testing environment, runs the backend test suite, PHPStan analysis, PHP_CodeSniffer linting, and builds the Blade/Vite assets.
