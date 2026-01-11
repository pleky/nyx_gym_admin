# Copilot Instructions - Nyx Gym Admin

## Project Overview
Laravel 12 application for gym administration. Uses PostgreSQL database (`nyx_gym_db`), Tailwind CSS v4 (with Vite integration), and Laravel Pint for code formatting.

## Architecture & Structure
- **Framework**: Laravel 12 (PHP 8.2+)
- **Database**: PostgreSQL (configured as default via `.env`)
  - Connection: `pgsql` (host: 127.0.0.1:5432)
  - Database: `nyx_gym_db`, user: `nyx_gym`
- **Frontend**: Tailwind CSS v4 with Vite 7
  - Entry points: [resources/css/app.css](resources/css/app.css), [resources/js/app.js](resources/js/app.js)
  - Config: [vite.config.js](vite.config.js) ignores `storage/framework/views` for performance
- **Authentication**: Standard Laravel authentication with User model ([app/Models/User.php](app/Models/User.php))

## Development Workflows

### Starting the Development Environment
Use the unified development command (runs server, queue worker, logs, and Vite concurrently):
```bash
composer dev
```

This starts 4 processes:
- PHP artisan serve (blue)
- Queue listener (purple)
- Laravel Pail logs (pink)
- Vite dev server (orange)

### Initial Setup
```bash
composer setup
```
This runs: install dependencies, copy `.env`, generate app key, run migrations, install npm packages, and build assets.

### Testing
```bash
composer test
```
Clears config cache and runs PHPUnit tests.

### Code Formatting
Use Laravel Pint (configured in project):
```bash
./vendor/bin/pint
```

## Project-Specific Conventions

### Database
- **Default connection**: PostgreSQL (not MySQL or SQLite)
- Migrations use standard Laravel blueprint syntax
- Default tables: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`

### Models & Eloquent
- Models in `app/Models/` extend appropriate base classes (`Authenticatable` for User)
- PSR-4 autoloading: `App\` namespace maps to `app/`

### Controllers
- Base controller: [app/Http/Controllers/Controller.php](app/Http/Controllers/Controller.php)
- Currently minimal controllers (starter project)

### Routes
- Web routes: [routes/web.php](routes/web.php)
- Console routes: [routes/console.php](routes/console.php)

### Frontend Assets
- Tailwind CSS v4 (latest) with Vite plugin
- Use `@vite` directive in Blade templates to include assets
- Dev server watches files except `storage/framework/views/**`

## Key Development Tools
- **Tinker**: `php artisan tinker` (REPL for testing code)
- **Pail**: Real-time log viewing (`php artisan pail`)
- **Sail**: Laravel Sail available but not currently in use (environment uses local PostgreSQL)
- **Queue**: Uses `database` driver by default

## Environment Configuration
- Never commit `.env` file
- Database credentials in `.env`: PostgreSQL connection required
- App key must be generated: `php artisan key:generate`

## Testing
- PHPUnit 11.5+ with Collision for error reporting
- Test structure: `tests/Feature/` for integration, `tests/Unit/` for unit tests
- Base test case: [tests/TestCase.php](tests/TestCase.php)

## Code Style
- Follow Laravel conventions and PSR-12 via Pint
- Type hints required (PHP 8.2+)
- Use Laravel's modern syntax (no facades in routes by default)
