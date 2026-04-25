# LaravelQuotes

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Tests][ico-tests]][link-tests]

A lightweight Laravel package that provides quote helpers with config-driven defaults.

## Installation

Via Composer

```bash
composer require mimisk/laravel-quotes
```

## Usage

Get all quotes:

```php
use Mimisk\LaravelQuotes\Facades\LaravelQuotes;

$quotes = LaravelQuotes::all();
```

Get one random quote:

```php
$quote = LaravelQuotes::random();
```

Override quotes in your app by publishing config:

```bash
php artisan vendor:publish --tag=laravel-quotes.config
```

## Change log

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
composer test
```

## Security

If you discover any security related issues, please email `mimisk88@gmail.com` instead of using the issue tracker.

## Credits

- [Mimis K][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [LICENSE file](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/mimisk/laravel-quotes.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mimisk/laravel-quotes.svg?style=flat-square
[ico-tests]: https://img.shields.io/github/actions/workflow/status/MimisK13/laravel-quotes/tests.yml?branch=main&label=tests&style=flat-square

[link-packagist]: https://packagist.org/packages/mimisk/laravel-quotes
[link-downloads]: https://packagist.org/packages/mimisk/laravel-quotes
[link-tests]: https://github.com/MimisK13/laravel-quotes/actions/workflows/tests.yml
[link-author]: https://github.com/mimisk
[link-contributors]: ../../contributors
