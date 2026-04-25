<?php

namespace Mimisk\LaravelQuotes;

use Illuminate\Support\Arr;

class LaravelQuotes
{
    /**
     * @return array<int, string>
     */
    public function all(): array
    {
        $quotes = config('laravel-quotes.quotes', []);

        if (! is_array($quotes) || $quotes === []) {
            return [];
        }

        return array_values(array_filter($quotes, fn (mixed $quote): bool => is_string($quote) && $quote !== ''));
    }

    public function random(): string
    {
        $quotes = $this->all();

        return $quotes === []
            ? 'No quotes available.'
            : Arr::random($quotes);
    }
}
