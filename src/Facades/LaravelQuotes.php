<?php

namespace Mimisk\LaravelQuotes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<int, string> all()
 * @method static string random()
 */
class LaravelQuotes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-quotes';
    }
}

