<?php

namespace Mimisk\LaravelQuotes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mimisk\LaravelQuotes\Enums\DiscountType;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;

class Quote extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_type',
        'owner_id',
        'number',
        'status',
        'title',
        'notes',
        'currency',
        'discount_type',
        'discount_value',
        'subtotal',
        'tax_total',
        'discount_total',
        'total',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'owner_id' => 'integer',

            'status' => QuoteStatus::class,
            'discount_type' => DiscountType::class,

            'discount_value' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'valid_until' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            config('laravel-quotes.models.quote_item')
        );
    }
}

