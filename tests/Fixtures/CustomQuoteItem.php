<?php

namespace Mimisk\LaravelQuotes\Tests\Fixtures;

use Mimisk\LaravelQuotes\Models\QuoteItem;

class CustomQuoteItem extends QuoteItem
{
    protected $table = 'quote_items';
}
