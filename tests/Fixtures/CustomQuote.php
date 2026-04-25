<?php

namespace Mimisk\LaravelQuotes\Tests\Fixtures;

use Mimisk\LaravelQuotes\Models\Quote;

class CustomQuote extends Quote
{
    protected $table = 'quotes';
}
