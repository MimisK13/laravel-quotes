<?php

namespace Mimisk\LaravelQuotes\Support;

final class GenerateQuoteNumber
{
    public function handle(): string
    {
        return sprintf(
            '%s%s-%s',
            config('laravel-quotes.number.prefix'),
            now()->format(config('laravel-quotes.number.date_format')),
            str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT)
        );
    }
}

