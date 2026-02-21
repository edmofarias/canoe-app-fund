# Canoe App Funds

A Laravel-based fund management API with event-driven duplicate detection, SQLite database, Redis event bus, and Vue.js frontend. The system manages investment funds, fund managers, and companies with comprehensive CRUD operations, soft delete support, and asynchronous duplicate warning generation.

## Features

- Fund management with aliases and company associations
- Fund manager and company tracking
- Event-driven duplicate detection using Redis
- Soft delete support for all entities
- Advanced filtering (by name, fund manager, year, company)
- RESTful API with comprehensive validation
- Vue.js frontend for user interaction
- Property-based testing for correctness validation

## Prerequisites

- PHP 8.1 or higher
- Composer
- Redis server (for event queue)
- Node.js and npm (for Vue.js frontend)
- SQLite (included with PHP)

## Installation

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Ensure your `.env` file has the following configuration:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

QUEUE_CONNECTION=redis
BROADCAST_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Create Database and Run Migrations

```bash
touch database/database.sqlite
php artisan migrate
```

### 4. (Optional) Seed Sample Data

```bash
php artisan db:seed
```

This creates sample fund managers, companies, and funds with aliases for testing.

### 5. Install Frontend Dependencies

```bash
npm install
```

## Running the Application

You need to run **three separate processes** for the full application:

### Terminal 1: Laravel API Server

```bash
php artisan serve
```

API available at: `http://localhost:8000`

### Terminal 2: Queue Worker (REQUIRED)

**CRITICAL:** The queue worker must be running for duplicate detection to work.

```bash
php artisan queue:work
```

This processes duplicate warning events from Redis asynchronously. Keep this running while using the application.

**Queue Worker Options:**
- `php artisan queue:work --tries=3` - Retry failed jobs up to 3 times
- `php artisan queue:work --timeout=60` - Set job timeout to 60 seconds
- `php artisan queue:work --sleep=3` - Sleep 3 seconds when no jobs available

**For Production:** Use a process manager like Supervisor to keep the queue worker running continuously.

### Terminal 3: Vue.js Frontend

```bash
npm run dev
```

Frontend available at: `http://localhost:5173`

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Groups

```bash
# Run only property-based tests
php artisan test --group=property-based

# Run only canoe-app-funds tests
php artisan test --group=canoe-app-funds

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

### Test Summary

- **35 tests** with **198 assertions**
- Unit tests for all API endpoints
- Property-based tests with 100 iterations
- Integration tests for event system
- Validation and error handling tests

## API Documentation

### Fund Endpoints

#### Create Fund
```http
POST /api/funds
Content-Type: application/json

{
  "name": "Tech Growth Fund",
  "start_year": 2020,
  "fund_manager_id": 1,
  "aliases": ["TGF", "Tech Fund"],
  "company_ids": [1, 2, 3]
}
```

#### List Funds (with filters)
```http
GET /api/funds?name=tech&fund_manager_id=1&start_year=2020&company_id=1
```

#### Get Fund
```http
GET /api/funds/{id}
```

#### Update Fund
```http
PUT /api/funds/{id}
Content-Type: application/json

{
  "name": "Updated Fund Name",
  "start_year": 2021,
  "aliases": ["New Alias"],
  "company_ids": [1, 2]
}
```

#### Delete Fund (Soft Delete)
```http
DELETE /api/funds/{id}
```

### Fund Manager Endpoints

```http
POST /api/fund-managers
GET /api/fund-managers
DELETE /api/fund-managers/{id}
```

### Company Endpoints

```http
POST /api/companies
GET /api/companies
DELETE /api/companies/{id}
```

### Duplicate Warning Endpoints

```http
GET /api/duplicate-warnings
```

Returns unresolved duplicate warnings with full fund details.

## Architecture

### Event-Driven Duplicate Detection

When a fund is created or updated, the system:

1. Checks for name/alias matches with existing funds (same fund manager)
2. Emits a `DuplicateFundWarning` event to Redis queue
3. Queue worker processes the event asynchronously
4. Warning is persisted to `duplicate_warnings` table
5. Warnings are available via `/api/duplicate-warnings` endpoint

This approach ensures API responsiveness while maintaining data quality.

### Database Schema

- `funds` - Investment funds with soft deletes
- `fund_managers` - Fund management companies with soft deletes
- `companies` - Investment recipients with soft deletes
- `aliases` - Alternative fund names (unique constraint)
- `company_fund` - Many-to-many pivot table
- `duplicate_warnings` - Detected duplicate fund entries

### Soft Deletes

All entities use soft deletes (`deleted_at` timestamp). Soft-deleted records:
- Are excluded from list operations
- Preserve all relationships
- Can be restored if needed
- Maintain referential integrity

## Configuration

### CORS Configuration

CORS is configured in `config/cors.php` to allow requests from the Vue.js dev server (`http://localhost:5173`). Update this for production environments.

### Queue Configuration

Queue configuration is in `config/queue.php`. The default queue connection is Redis. Ensure Redis is running before starting the queue worker.

### Database Configuration

SQLite is used for simplicity. For production, consider PostgreSQL or MySQL. Update `config/database.php` and `.env` accordingly.

## Troubleshooting

### Queue Worker Not Processing Events

- Ensure Redis is running: `redis-cli ping` (should return `PONG`)
- Check queue worker is running: `php artisan queue:work`
- Verify `.env` has `QUEUE_CONNECTION=redis`
- Check Laravel logs: `storage/logs/laravel.log`

### Database Errors

- Ensure database file exists: `touch database/database.sqlite`
- Run migrations: `php artisan migrate`
- Check file permissions on `database/` directory

### Frontend Not Connecting to API

- Verify API is running on `http://localhost:8000`
- Check CORS configuration in `config/cors.php`
- Verify API base URL in `resources/js/api.js`

### Tests Failing

- Ensure test database is configured (uses in-memory SQLite by default)
- Run migrations: `php artisan migrate --env=testing`
- Clear cache: `php artisan config:clear`

## Development Notes

- Property-based tests run 100 iterations with randomized data
- All tests are tagged with `@group canoe-app-funds`
- Property tests are tagged with `@group property-based`
- Factories use Faker for realistic test data generation
- Database transactions are used for all write operations

## Project Structure

```
app/
├── Events/              # Event classes (DuplicateFundWarning)
├── Http/Controllers/    # API controllers
├── Listeners/           # Event listeners
├── Models/              # Eloquent models
└── Services/            # Business logic services

database/
├── factories/           # Model factories for testing
├── migrations/          # Database migrations
└── seeders/             # Database seeders

resources/js/
├── components/          # Vue.js components
├── views/               # Vue.js views
├── api.js               # API service layer
└── router.js            # Vue Router configuration

tests/
├── Feature/             # Feature and integration tests
└── Unit/                # Unit tests
```