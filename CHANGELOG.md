# Changelog

All notable changes to `LaravelQuotes` will be documented in this file.

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
