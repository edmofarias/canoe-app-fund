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

- PHP 8.3 or higher
- Composer
- Docker and Docker Compose (for Redis)
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
DB_DATABASE=database/database.sqlite

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

### Prerequisites

### Start Redis Server

The application uses Redis for event queuing. Start Redis using Docker Compose:

```bash
docker-compose up -d
```

This will:
- Pull the Redis image
- Start Redis on port 6379
- Run Redis in the background

You need to run **three separate processes** for the full application locally:

### Terminal 1: Laravel API Server

```bash
php artisan serve
```

### Terminal 2: Queue Worker (REQUIRED)

The queue worker must be running for duplicate fund detection to work.

```bash
php artisan queue:work --queue=duplicate_fund_warning
```

### Terminal 3: Vue.js Frontend

```bash
npm run dev
```
API available at: `http://localhost:8000`

## Running Tests

### Run All Tests

```bash
php artisan test
```

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

All entities use soft deletes (`deleted_at` timestamp). 

## Troubleshooting

### Queue Worker Not Processing Events

- Ensure Redis is running: `docker-compose ps` (should show `canoe-redis` as running)
- Test Redis connection: `docker exec -it canoe-redis redis-cli ping` (should return `PONG`)
- Check queue worker is running: `php artisan queue:work`
- Verify `.env` has `QUEUE_CONNECTION=redis`
- Check Laravel logs: `storage/logs/laravel.log`
- Restart Redis if needed: `docker-compose restart redis`

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