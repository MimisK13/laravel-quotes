<?php

namespace Mimisk\LaravelQuotes\Facades;

use Illuminate\Support\Facades\Facade;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Models\Quote;

/**
 * @method static Quote create(QuoteData $data)
 * @method static Quote update(Quote $quote, QuoteData $data)
 * @method static Quote send(Quote $quote)
 * @method static Quote accept(Quote $quote)
 * @method static Quote reject(Quote $quote)
 * @method static Quote expire(Quote $quote)
 * @method static void delete(Quote $quote)
 */
final class Quotes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'quotes';
    }
}
