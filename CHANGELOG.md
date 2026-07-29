# Changelog

All notable changes to `stickleapp-ui` will be documented in this file.

## Unreleased

### Changed

-   **BREAKING** Stickle's UI, read API and broadcast channels now require a `viewStickle` Gate. See `UPGRADE.md`.
-   **BREAKING** `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` are no longer read; the values are arrays in `config/stickle.php`.
-   `stickle:install` no longer prompts for route middleware and prints the Gate definition on completion.
