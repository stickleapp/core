# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build/Lint/Test Commands

- Build workbench: `composer build` or `php vendor/bin/testbench workbench:build --ansi`
- Run dev server: `composer serve` or `php vendor/bin/testbench serve --ansi`
- Lint code: `composer lint` (runs both Laravel Pint and PHPStan)
- Format code: `composer format` or `vendor/bin/pint`
- Static analysis: `composer analyse` or `vendor/bin/phpstan analyse`
- Run all tests: `composer test` or `vendor/bin/pest`
- Run a single test: `vendor/bin/pest tests/Unit/ExampleTest.php` or with filter: `vendor/bin/pest --filter=ExampleTest`

## Code Style Guidelines

- PHP version: ^8.2
- Laravel version: ^10.0 or ^11.0
- PSR-12 code style (enforced by Laravel Pint)
- PHPStan level 6 for static analysis
- Sort imports alphabetically
- Files should use UTF-8 encoding without BOM
- Use camelCase for method names, variables, and properties
- Use PascalCase for class names, interfaces, and enums
- Avoid abbreviations in names
- Add appropriate PHPDoc comments for complex methods
- Avoid magic numbers and "stringly typed" code
- Prefer type hints and return types for methods
- Use strict comparison operators (=== and !==)