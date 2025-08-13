# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Stickle is a Laravel package for customer analytics and engagement. It tracks user behavior, model attributes, and provides segmentation capabilities. The package is currently pre-release software.

## Key Commands

### PHP/Laravel Commands

-   `composer install` - Install PHP dependencies
-   `php artisan stickle:configure` - Configure Stickle and set defaults
-   `php artisan migrate` - Run database migrations

### Development Commands

-   `composer run test` - Run tests using Pest
-   `composer run lint` - Run Pint code formatter and PHPStan analysis
-   `composer run format` - Format code with Pint
-   `composer run analyse` - Run PHPStan static analysis
-   `composer run serve` - Build and serve the workbench application
-   `composer run build` - Build the workbench

### Frontend Commands

-   `npm install` - Install Node dependencies
-   `npm run dev` - Start Vite development server
-   `npm run build` - Build assets for production
-   `npm run docs:dev` - Start VitePress documentation server

## Architecture

### Core Components

**Tracking System**: Client-side JavaScript tracking and server-side event logging for user behavior, authentication events, and model attribute changes.

**Segment System**: Define customer segments as PHP classes in the workbench/app/Segments/ directory. Segments are tracked over time with statistics.

**Analytics Pipeline**: Background jobs process data into statistics and exports:

-   `RecordSegmentStatisticsCommand` - Calculate segment statistics
-   `RecordModelRelationshipStatisticsCommand` - Track model relationships
-   `ExportSegmentsCommand` - Export segment data

**Event System**: Comprehensive event tracking including:

-   Authentication events (via `AuthenticatableEventListener`)
-   Model attribute changes (via `ModelAttributeChangedListener`)
-   Page views and user interactions
-   Custom server-side events

### Key Directories

-   `src/` - Core package source code
-   `workbench/` - Laravel test application for development
-   `database/` - Migrations and seeders for core tables
-   `resources/js/tracking/` - JavaScript tracking SDK
-   `src/Models/` - Eloquent models for analytics data
-   `src/Filters/` - Query filtering system for segments
-   `src/Http/Controllers/` - API endpoints for data ingestion and retrieval

### Data Models

**StickleEntity Trait**: Applied to User and optionally Group models to enable tracking.

**Core Models**:

-   `ModelAttributes` - Stores tracked model attributes
-   `Segment` - Defines customer segments
-   `SegmentStatistic` - Time-series segment data
-   `ModelRelationshipStatistic` - Relationship tracking data

### Configuration

Primary configuration in `config/stickle.php` with options for:

-   Database connection and table prefixes
-   Tracking behavior (client/server)
-   User/Group model relationships
-   Job scheduling frequencies

## Testing

Uses Pest for testing with PHPStan for static analysis. Test structure:

-   Unit tests in `tests/Unit/`
-   Feature tests in `tests/Feature/`
-   Architecture tests in `ArchTest.php`

## Development Environment

The workbench application (`workbench/`) provides a complete Laravel environment for testing the package with sample models (User, Customer, Subscription) and segments.

===

<laravel-boost-guidelines>
=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms
</laravel-boost-guidelines>