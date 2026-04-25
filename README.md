# LaravelQuotes

[![Tests][ico-tests]][link-tests]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![codecov](https://codecov.io/gh/MimisK13/laravel-quotes/graph/badge.svg?token=JO6J91OD6C)](https://codecov.io/gh/MimisK13/laravel-quotes)

A lightweight Laravel package that provides quote helpers with config-driven defaults.

## Installation

Via Composer

```bash
composer require mimisk/laravel-quotes
```

## Usage

Publish config (optional):

```bash
php artisan vendor:publish --tag=quotes-config
```

Create a quote (owner can be any morphable Eloquent model, e.g. `Customer`):

```php
use Mimisk\LaravelQuotes\Actions\CreateQuoteAction;
use Mimisk\LaravelQuotes\DTOs\QuoteData;

$quote = app(CreateQuoteAction::class)->handle(QuoteData::fromArray([
    'owner' => $customer,
    'title' => 'Customer Products Quote',
    'currency' => 'EUR',
    'discount_type' => 'fixed', // fixed | percentage
    'discount_value' => 50,
    'items' => [
        [
            'name' => 'Product A',
            'quantity' => 2,
            'unit_price' => 120,
            'tax_rate' => 24,
        ],
        [
            'name' => 'Product B',
            'quantity' => 1,
            'unit_price' => 85,
            'tax_rate' => 24,
        ],
    ],
]));
```

Update a draft quote:

```php
use Mimisk\LaravelQuotes\Actions\UpdateQuoteAction;
use Mimisk\LaravelQuotes\DTOs\QuoteData;

app(UpdateQuoteAction::class)->handle(
    $quote,
    QuoteData::fromArray([
        'owner' => $customer,
        'title' => 'Updated Customer Quote',
        'items' => [
            [
                'name' => 'Product A (Updated)',
                'quantity' => 3,
                'unit_price' => 110,
                'tax_rate' => 24,
            ],
        ],
    ])
);
```

Status transitions:

```php
use Mimisk\LaravelQuotes\Actions\AcceptQuoteAction;
use Mimisk\LaravelQuotes\Actions\ExpireQuoteAction;
use Mimisk\LaravelQuotes\Actions\RejectQuoteAction;
use Mimisk\LaravelQuotes\Actions\SendQuoteAction;

app(SendQuoteAction::class)->handle($quote);    // draft -> sent
app(AcceptQuoteAction::class)->handle($quote);  // sent -> accepted
app(RejectQuoteAction::class)->handle($quote);  // sent -> rejected
app(ExpireQuoteAction::class)->handle($quote);  // sent -> expired
```

Delete a quote:

```php
use Mimisk\LaravelQuotes\Actions\DeleteQuoteAction;

app(DeleteQuoteAction::class)->handle($quote); // only draft or rejected
```

## Events

The package dispatches the following events:

- QuoteCreated
- QuoteUpdated
- QuoteSent
- QuoteAccepted
- QuoteRejected
- QuoteExpired

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
