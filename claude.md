# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## IMPORTANT: Sound Notification

After finishing responding to my request or running a command, run this command to notify me by sound:

```bash
afplay ~/airhorn.aiff
```

## What is Stickle Core?

Stickle Core is a Laravel package for customer analytics and engagement. It provides:
- Client-side (JS) and server-side event tracking
- User segmentation defined as code
- Model attribute tracking and auditing
- Real-time analytics via WebSockets
- REST API for event ingestion

**Requirements:** PHP 8.3+, Laravel 11+, PostgreSQL (required for partitioned tables)

## Commands

### Development
```bash
composer dev          # Build and run dev server with hot reload
composer serve        # Build and serve with testbench
composer start        # Build and serve application
```

### Testing & Quality
```bash
composer test         # Run Pest tests
composer test-coverage # Run tests with coverage
composer analyse      # Run PHPStan (level 8)
composer lint         # Run Pint + PHPStan
composer format       # Run Pint (dirty files only)
composer refactor     # Run Rector
composer verify       # Full verify: refactor + format + test + analyse + npm build
composer pre-commit   # Dry-run checks for pre-commit
```

### Single Test
```bash
php -d memory_limit=1G vendor/bin/pest --filter="test name"
php -d memory_limit=1G vendor/bin/pest tests/Unit/Filters/Tests/EqualsTest.php
```

### Database
```bash
composer migrate:seed  # Fresh migrate + seed with workbench seeder
```

### Frontend
```bash
npm run build         # Build JS/CSS assets
npm run dev           # Vite dev server
npm run docs:dev      # VitePress documentation dev server
```

## Architecture

### Package Structure (src/)
- **CoreServiceProvider.php** - Main service provider; registers middleware, routes, commands
- **EventServiceProvider.php** - Authentication event listeners
- **ScheduleServiceProvider.php** - Scheduled tasks for analytics processing

### Key Concepts

**StickleEntity Trait** - Add to any Eloquent model for tracking:
```php
use StickleApp\Core\Traits\StickleEntity;

class User extends Model {
    use StickleEntity;

    // Observe changes to these attributes
    public static function stickleObservedAttributes(): array {
        return ['email', 'name'];
    }

    // Track these attributes over time
    public static function stickleTrackedAttributes(): array {
        return ['subscription_status'];
    }
}
```

**Segments** - Define segments as classes extending `SegmentContract`:
```php
class ActiveUsers extends SegmentContract {
    public string $model = User::class;

    public function toBuilder(): Builder {
        return User::query()->where('last_active_at', '>', now()->subDays(30));
    }
}
```

**Filters** - Query builder filters in `src/Filters/`:
- `Targets/` - What to filter on (Number, Date, EventCount, Segment, etc.)
- `Tests/` - How to test (Equals, GreaterThan, Between, IsInSegment, etc.)

### Workbench
The `workbench/` directory contains a full Laravel app for development/testing:
- `workbench/app/Models/` - Test models (User, Account, etc.)
- `workbench/app/Segments/` - Example segment definitions
- Uses Orchestra Testbench for package testing

### Configuration
Published to `config/stickle.php`. Key settings:
- `database.tablePrefix` - Default: `stc_`
- `database.partitionsEnabled` - PostgreSQL table partitioning
- `namespaces.segments` - Where segment classes live (default: `App\Segments`)
- `namespaces.models` - Where models live (default: `App\Models`)
- `routes.api.prefix` - API route prefix (default: `stickle/api`)

### Partitioned Tables
Stickle uses PostgreSQL partitioning for high-volume tables. Create partitions with:
```bash
php artisan stickle:create-partitions {table} {schema} {interval} {date} {count}
```

## Foundational Context

This is a Laravel package. Use solutions from the Laravel ecosystem:
- The Laravel Framework
- Official Laravel Products (Forge, Nova, Nightwatch, Cloud)
- Official Laravel Packages

NEVER include 3rd-party packages without permission.

## Laravel Boost (MCP Tools)

Laravel Boost provides MCP tools for this project:
- `search-docs` - Search Laravel ecosystem documentation (always use first)
- `tinker` - Execute PHP to debug or query models
- `database-query` - Direct database reads
- `browser-logs` - Read browser console logs
- `list-artisan-commands` - Check artisan command parameters
- `get-absolute-url` - Get correct URLs for the user
