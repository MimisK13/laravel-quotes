# Changelog

All notable changes to `LaravelQuotes` will be documented in this file.

## 0.0.4 - 2026-04-26

### Changed
- Renamed config key `quotes.valid_until.default_days` to `quotes.validity.default_days`.
- Updated quote creation logic to read default quote validity from `quotes.validity.default_days`.

### Removed
- Removed `statuses` configuration section from `config/quotes.php`.

## 0.0.3 - 2026-04-25

### Added
- Added `quotes:expire` Artisan command to expire `sent` quotes with `valid_until` in the past.
- Added package documentation for quote expiration automation and scheduler usage.

### Changed
- Registered `quotes:expire` command in the package service provider when running in console.
- Standardized new command/service provider namespace references to `Mimisk\\...` for consistent autoloading.

## 0.0.2 - 2026-04-25

### Added
- Introduced `InvalidQuoteTransition` domain exception for invalid quote state transitions.
- Added explicit exception messages for already processed transitions (`alreadySent`, `alreadyAccepted`, `alreadyRejected`, `alreadyExpired`).
- Added README guidance for handling quote transition exceptions (try/catch and global handler examples).

### Changed
- Updated quote lifecycle actions to throw `InvalidQuoteTransition` instead of generic `RuntimeException`.
- Updated feature tests to assert the new domain exception type.

## 0.0.1 - 2026-04-25

### Added
- Initial package release for Laravel `12|13` and PHP `8.4`.
- Core quote flow actions: create, update, send, accept, reject, expire, delete.
- `Quote` and `QuoteItem` models, migrations, enums (`QuoteStatus`, `DiscountType`), DTOs, events, and support helpers.
- Service container binding (`quotes`), `QuotesService`, and `Quotes` facade.
- Config file `quotes.php` with defaults for currency, tax, discount, number generation, statuses, owner morph name, and overridable model classes.
- Pest test suite, GitHub Actions tests workflow, and static analysis (Larastan/PHPStan) setup.

### Changed
- Standardized package config key/file to `quotes`.
- Improved relationship compatibility for overridden quote/quote item models using explicit `quote_id` relation keys.
- Updated README usage and package metadata to match current implementation.
