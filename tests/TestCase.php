<?php

namespace Mimisk\LaravelQuotes\Tests;

use Mimisk\LaravelQuotes\LaravelQuotesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelQuotesServiceProvider::class,
        ];
    }
}
