<?php

namespace Mimisk\LaravelQuotes\Support;

final class GenerateQuoteNumber
{
    public function handle(): string
    {
        $prefix = (string) config('quotes.number.prefix');
        $date = now()->format((string) config('quotes.number.date_format'));
        $separator = (string) config('quotes.number.separator', '-');
        $length = max(1, (int) config('quotes.number.random_length', 4));
        $max = max(1, ((10 ** $length) - 1));
        $random = str_pad((string) random_int(1, $max), $length, '0', STR_PAD_LEFT);

        return sprintf(
            '%s%s%s%s',
            $prefix,
            $date,
            $separator,
            $random
        );
    }
}
