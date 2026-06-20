# Simple Transaction Webapp

A Laravel application for managing simple financial transactions against a chart of accounts. The application lives in `backend-laravel` and uses Laravel Blade with the Vite asset pipeline. There is no separate frontend app(at least for now).

## What Is Included

- Transaction entry with date, chart-of-account selection, description, idempotency key, and amount handling.
- Chart of account category management.
- Chart of account management with income and expense account types.
- Server-side listing pages powered by Yajra DataTables.
- AdminLTE 4 based Blade layout and UI.
- Monthly income, expense, and net-income reports by category.
- XLSX report export using PhpSpreadsheet.
- Database seeders for starter chart-of-account and transaction data.
- Feature tests for transactions, master data, and reports.

## Development Quality Features

This project is set up with repeatable local and CI checks:

- Docker Compose stack for MySQL, phpMyAdmin, Laravel backend, and Laravel Vite assets.
- Xdebug installed in the backend image and configured through `docker/backend/xdebug.ini`.
- GitHub Actions CI in `.github/workflows/ci.yml`.
- PHPUnit feature test coverage through `composer test`.
- Larastan/PHPStan static analysis through `composer analyse`.
- PHP_CodeSniffer coding-standard checks through `composer lint`.
- Automatic PHPCS fixing available through `composer lint:php:fix`.
- Composer manifest validation in CI with `composer validate --strict`.
- Blade/Vite production asset build through `npm run build`.
- Repository contracts bound to concrete repositories in Laravel's service container.
- Form Request validation for write flows.
- Database transactions around transaction create/update workflows.

## Stack

- Laravel 13
- PHP 8.3+
- MySQL 8.4
- Blade, Vite, Bootstrap 5, AdminLTE 4, and Bootstrap Icons
- Yajra Laravel DataTables
- PhpSpreadsheet
- PHPUnit 12
- Larastan/PHPStan
- PHP_CodeSniffer
- Docker Compose

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

The backend image installs Xdebug and loads `docker/backend/xdebug.ini`. It listens on port `9003` and uses `host.docker.internal` as the client host, which works well with common IDE debugging setups on Docker Desktop.

## Local Development Without Docker

Configure a local MySQL database first, then run:

```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
composer dev
```

The Laravel `.env.example` defaults to the same MySQL database name and credentials used by Docker.

`composer dev` starts the Laravel server, queue listener, Laravel Pail logs, and the Vite dev server together.

## Useful Commands

Run these from `backend-laravel`:

```bash
composer test
composer analyse
composer lint
composer lint:php:fix
npm run build
```

For a full first-time application setup from inside `backend-laravel`, the Composer `setup` script installs PHP dependencies, creates `.env` if needed, generates the app key, runs migrations, installs npm dependencies, and builds assets:

```bash
composer setup
```

## Continuous Integration

GitHub Actions runs the Simple Transaction CI workflow after pushes and pull requests to `main` and `development`. The workflow:

- validates `composer.json`;
- installs Composer dependencies;
- prepares a Laravel testing environment;
- runs the backend test suite;
- runs PHPStan/Larastan analysis;
- runs PHP_CodeSniffer;
- installs npm dependencies with `npm ci`;
- builds Blade/Vite assets.

CI uses SQLite in memory for fast backend tests.

## Project Structure

```text
backend-laravel/           Laravel application
backend-laravel/app/       Controllers, models, requests, repositories, services
backend-laravel/resources/ Blade views and Vite assets
backend-laravel/routes/    Web and API routes
backend-laravel/tests/     PHPUnit feature and unit tests
docker/backend/            Nginx, PHP-FPM, Supervisor, and Xdebug support files
.github/workflows/         GitHub Actions CI workflow
```
