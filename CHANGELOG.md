# Changelog

All notable changes to `stickleapp-ui` will be documented in this file.

## Unreleased

### Changed

-   **BREAKING** Stickle's UI and read API now require a `viewStickle` Gate. See `UPGRADE.md`.
-   **BREAKING** `STICKLE_WEB_MIDDLEWARE` and `STICKLE_API_MIDDLEWARE` are no longer read; the values are arrays in `config/stickle.php`.
-   `stickle:install` no longer prompts for route middleware and prints the Gate definition on completion.

### Fixed

-   Stickle's assets are now published under the `laravel-assets` tag as well as `package-assets`, so the default Laravel application's `post-update-cmd` refreshes them on upgrade without an app-specific publish step.
-   **Security** Stickle's events now broadcast on private channels, so realtime subscriptions are authorized against `viewStickle` instead of being open to anyone holding the application's Reverb/Pusher app key. Requires `Broadcast::routes()` in your application; see `UPGRADE.md`.
