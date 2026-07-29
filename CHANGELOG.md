# Changelog

All notable changes to `stickleapp-ui` will be documented in this file.

## Unreleased

### Changed

-   **BREAKING** Stickle's UI and read API now require a `viewStickle` Gate. See `UPGRADE.md` — this does not close the broadcast channels; that stream remains unauthenticated.
-   **BREAKING** `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` are no longer read; the values are arrays in `config/stickle.php`.
-   `stickle:install` no longer prompts for route middleware and prints the Gate definition on completion.
