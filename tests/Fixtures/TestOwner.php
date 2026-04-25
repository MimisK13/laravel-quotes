<?php

namespace Mimisk\LaravelQuotes\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestOwner extends Model
{
    protected $table = 'quote_test_owners';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];
}
