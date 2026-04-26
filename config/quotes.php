<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Defines the default currency used for quotes.
    | Must be a valid ISO 4217 currency code (e.g. EUR, USD, GBP).
    |
    */

    'currency' => 'EUR',

    /*
    |--------------------------------------------------------------------------
    | Number Generation
    |--------------------------------------------------------------------------
    |
    | Defines how quote numbers are generated.
    |
    | Example output:
    | Q-20260426-1234
    |
    | Components:
    | - prefix: Static prefix added at the beginning
    | - date_format: PHP date format applied to current date
    | - separator: Separator between parts
    | - random_length: Length of the random numeric suffix
    |
    */

    'number' => [
        'prefix' => 'Q-',
        'date_format' => 'Ymd',
        'separator' => '-',
        'random_length' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Validity
    |--------------------------------------------------------------------------
    |
    | Defines the default number of days a quote remains valid.
    | This value is used when no explicit "valid_until" date is provided.
    |
    */

    'validity' => [
        'default_days' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    |
    | Defines the default tax rate (percentage) applied to quotes.
    | This value is used when no custom tax rate is provided.
    |
    */

    'tax' => [
        'default_rate' => 24.0, // percentage (e.g. 24.0 = 24%)
    ],

    /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    |
    | Defines the default discount type used by the package.
    |
    | Available options:
    | - fixed
    | - percentage
    |
    */

    'discount' => [
        'default_type' => 'fixed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models (Overridable)
    |--------------------------------------------------------------------------
    |
    | These models are used internally by the package.
    | You may override them by extending the base models and updating
    | the configuration below.
    |
    | Example:
    | App\Models\Quote extends \Mimisk\LaravelQuotes\Models\Quote
    |
    */

    'models' => [
        'quote' => \Mimisk\LaravelQuotes\Models\Quote::class,
        'quote_item' => \Mimisk\LaravelQuotes\Models\QuoteItem::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Owner (Morph Name)
    |--------------------------------------------------------------------------
    |
    | Defines the morph name used for the "owner" relationship.
    | This is used for polymorphic relations (morphTo / morphMany).
    |
    | Example:
    | $quote->owner() => User | Company | Team
    |
    | The same morph name must be used consistently across your application.
    |
    */

    'owner' => [
        'morph_name' => 'owner',
    ],
];
